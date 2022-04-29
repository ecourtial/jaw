<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\Webhook;
use App\Event\ResourceEvent;
use App\Search\SearchResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostRepository extends ServiceEntityRepository implements ApiSimpleFilterResultInterface, ApiMultipleFiltersResultInterface
{
    private const AVAILABLE_COLUMN_FILTERS = [
        'title' => ['source' => 'column',],
        'createdAt' => ['source' => 'column', ],
        'updatedAt' => ['source' => 'column', ],
        'publishedAt' => ['source' => 'column', ],
        'online' => ['source' => 'column', ],
        'topPost' => ['source' => 'column', ],
        'language' => ['source' => 'column', ],
        'obsolete' => ['source' => 'column', ],
        'category' => ['source' => 'alias', 'columnName' => 'categoryId',],
        'author' => ['source' => 'alias', 'columnName' => 'authorId', ],
    ];

    public function __construct(ManagerRegistry $registry, private readonly EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($registry, Post::class);
    }

    public function save(Post $post): void
    {
        $actionType = ($post->getId() === null) ? Webhook::RESOURCE_ACTION_CREATION : Webhook::RESOURCE_ACTION_EDITION;
        $this->getEntityManager()->persist($post);
        $this->getEntityManager()->flush();
        $this->eventDispatcher->dispatch(new ResourceEvent($post, $actionType), ResourceEvent::NAME);
    }

    public function delete(Post $post): void
    {
        $this->eventDispatcher->dispatch(new ResourceEvent($post, Webhook::RESOURCE_ACTION_DELETION), ResourceEvent::NAME);
        $this->getEntityManager()->remove($post);
        $this->getEntityManager()->flush();
    }

    /**
     * For internal (Admin) search only.
     *
     * @return \App\Search\SearchResult[]
     */
    public function search(string $keywords, int $limit): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('p.id, p.title, p.slug, p.createdAt, p.updatedAt, p.publishedAt, c.id as categId, c.title as categTitle, c.slug as categSlug');

        $qb->from(Post::class, 'p');
        $qb->from(Category::class, 'c');

        $qb->where('p.title' . ' LIKE :request');
        $qb->orWhere('p.summary' . ' LIKE :request');
        $qb->orWhere('p.content' . ' LIKE :request');
        $qb->andWhere('p.category = c.id');
        $qb->orderBy('p.createdAt', 'DESC');
        $qb->setMaxResults($limit);
        $qb->setParameter('request', "%$keywords%");

        $result = $qb->getQuery()->getResult();

        if (empty($result)) {
            $result = [];
        }

        $processedResult = [];

        foreach ($result as $entry) {
            $processedResult[] = new SearchResult(
                $entry['id'],
                $entry['title'],
                $entry['slug'],
                $entry['createdAt'],
                $entry['updatedAt'],
                $entry['publishedAt'],
                $entry['categId'],
                $entry['categTitle'],
                $entry['categSlug'],
            );
        }

        return $processedResult;
    }

    // Filter on fields that are UNIQUE in the entity
    public function getByUniqueApiFilter(string $filter, string|int $param): array
    {
        $qb = $this->getMainSelect();

        if ($filter === 'slug' || $filter === 'id') {
            $qb->where('p.' . $filter . ' = :param');
            $qb->setParameter('param', $param);
        } else {
            throw new \LogicException('Unsupported filter: ' . $filter);
        }

        $result = $qb->getQuery()->getSingleResult();

        return $this->formatDate($result);
    }

    // Unlike the 'getByUniqueApiFilter'method, unknown filters are ignored.
    public function getByMultipleApiFilters(array $params): array
    {
        $qb = $this->getMainSelect();

        // Just to init the where clauses (useless clause otherwise)
        $qb->where('p.id IS NOT NULL');

        // First, basic filters on value
        foreach ($params as $filter => $value) {
            if (true === \array_key_exists($filter, self::AVAILABLE_COLUMN_FILTERS)) {
                if (self::AVAILABLE_COLUMN_FILTERS[$filter]['source'] === 'column') {
                    $qb->andWhere('p.' . $filter . ' = :' . $filter);
                } elseif (self::AVAILABLE_COLUMN_FILTERS[$filter]['source'] === 'alias') {
                    $qb->andHaving(self::AVAILABLE_COLUMN_FILTERS[$filter]['columnName'] . ' = :' . $filter);
                } else {
                    continue;
                }

                $qb->setParameter($filter, $value);
            }
        }

        // Filter on keywords
        if (\array_key_exists('keywords', $params)) {
            $qb->andWhere('p.title' . ' LIKE :request');
            $qb->orWhere('p.summary' . ' LIKE :request');
            $qb->orWhere('p.content' . ' LIKE :request');

            $qb->setParameter('request', "%{$params['keywords']}%");
        }

        // Now: ordering
        $this->addOrderForFiltering($qb, $params);

        // Pagination
        $limit = 10;
        $page = 1;

        if (\array_key_exists('limit', $params)) {
            $limit = (int)$params['limit'];
        }

        if (\array_key_exists('page', $params)) {
            $page = (int)$params['page'];
            $page = ($page < 1) ? 1 : $page;
            $offset = ($page * $limit) - $limit;
            $qb->setFirstResult($offset);
        }

        $qb->setMaxResults($limit);

        // Result
        $paginator = new Paginator($qb->getQuery(), true);
        $paginator->setUseOutputWalkers(false);
        $totalCount = \count($paginator);
        $totalPageCount = ceil($totalCount/$limit);

        $result =$paginator->getQuery()->getResult();

        if (null === $result) {
            $result = [];
        }

        foreach ($result as $index => $entry) {
            $result[$index] = $this->formatDate($entry);
        }

        return [
            'resultCount' => \count($result),
            'totalResultCount' => $totalCount,
            'page' => $page,
            'totalPageCount' => $totalPageCount,
            'posts' => $result,
        ];
    }

    private function getMainSelect(): QueryBuilder
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select(
            'p.id, p.title, p.summary, p.createdAt, p.updatedAt, p.publishedAt, p.slug, p.online, p.topPost, '
            . 'p.language, p.obsolete, IDENTITY(p.category) as categoryId, IDENTITY(p.author) as authorId, p.content'
        );
        $qb->from(Post::class, 'p');

        return $qb;
    }

    private function formatDate(array $result): array
    {
        $result['createdAt'] = $result['createdAt']->format(\DateTimeInterface::ATOM);
        $result['updatedAt'] = $result['updatedAt']->format(\DateTimeInterface::ATOM);
        $result['publishedAt'] =  $result['publishedAt'] === null ? null : $result['publishedAt']->format(\DateTimeInterface::ATOM);

        return $result;
    }

    private function addOrderForFiltering(QueryBuilder $qb, $params): void
    {
        if (true === \array_key_exists('orderByField', $params)
            && true === \is_array($params['orderByField'])
        ) {
            foreach ($params['orderByField'] as $column => $order) {
                $order = strtoupper($order);

                if (true === \array_key_exists($column, self::AVAILABLE_COLUMN_FILTERS)
                    && true === \in_array($order, ['ASC', 'DESC'])
                ) {
                    $qb->orderBy('p.' . $column , $order);
                }
            }
        }
    }
}
