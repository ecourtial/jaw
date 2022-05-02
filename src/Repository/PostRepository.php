<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\Webhook;
use App\Event\ResourceEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostRepository extends ServiceEntityRepository implements ApiSimpleFilterResultInterface, ApiMultipleFiltersResultInterface
{
    private const AVAILABLE_COLUMN_FILTERS = [
        'id' => ['source' => 'column',],
        'title' => ['source' => 'column',],
        'createdAt' => ['source' => 'column', 'columnName' => 'created_at',],
        'updatedAt' => ['source' => 'column', 'columnName' => 'updated_at',],
        'publishedAt' => ['source' => 'column', 'columnName' => 'published_at',],
        'online' => ['source' => 'column', ],
        'topPost' => ['source' => 'column', ],
        'language' => ['source' => 'column', ],
        'obsolete' => ['source' => 'column', ],
        'category' => ['source' => 'alias', 'columnName' => 'category_id',],
        'author' => ['source' => 'alias', 'columnName' => 'author_id', ],
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
    public function search(string $keywords, int $limit = 30, int $page = 1): array
    {
        // Unfortunately, the Paginator does not work with two FROM, so we have to do it manually.
        $totalResultCount =  $this->getEntityManager()->getConnection()->executeQuery(
            'SELECT COUNT(*) as count FROM posts'
            . ' WHERE title LIKE :request'
            . ' OR summary LIKE :request'
            . ' OR content LIKE :request',
            ['request' => "%$keywords%"]
        )
        ->fetchAssociative()['count'];

        // Get Results
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('p.id, p.title, p.slug, p.createdAt, c.id as categoryId, c.title as categoryTitle');

        $qb->from(Post::class, 'p');
        $qb->from(Category::class, 'c');

        $qb->where('p.title' . ' LIKE :request');
        $qb->orWhere('p.summary' . ' LIKE :request');
        $qb->orWhere('p.content' . ' LIKE :request');
        $qb->andWhere('p.category = c.id');
        $qb->orderBy('p.createdAt', 'DESC');

        $page = ($page < 1) ? 1 : $page;
        $offset = ($page * $limit) - $limit;
        $totalPageCount = (int)ceil($totalResultCount/$limit);
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);

        $qb->setParameter('request', "%$keywords%");

        $result = $qb->getQuery()->getResult();

        if (empty($result)) {
            $result = [];
        }

        $processedResult = [
            'resultCount' => \count($result),
            'totalResultCount' => $totalResultCount,
            'page' => $page,
            'totalPageCount' => $totalPageCount,
            'posts' => [],
        ];

        foreach ($result as $entry) {
            $processedResult['posts'][] = [
                'id' => $entry['id'],
                'title' => $entry['title'],
                'createdAt' => $entry['createdAt'],
                'categoryId' => $entry['categoryId'],
                'categoryTitle' => $entry['categoryTitle'],
            ];
        }

        return $processedResult;
    }

    // Filter on fields that are UNIQUE in the entity
    public function getByUniqueApiFilter(string $filter, string|int $param): array
    {
        $query = $this->getMainSelectQuery();

        if ($filter === 'slug' || $filter === 'id') {
            $query .= " WHERE $filter = :param LIMIT 1";
        } else {
            throw new \LogicException('Unsupported filter: ' . $filter);
        }

        $result = $this->getEntityManager()->getConnection()->executeQuery($query, ['param' => $param])->fetchAssociative();

        if (false === $result) {
            throw new NoResultException();
        }

        return $this->formatPostForResponse($result);
    }

    // Unlike the 'getByUniqueApiFilter'method, unknown filters are ignored.
    public function getByMultipleApiFilters(array $params): array
    {
        // Creation of the second par of the query (the conditions)
        $query = 'WHERE id IS NOT NULL ';
        $queryParams = [];

        // First, basic filters on value
        foreach ($params as $filter => $value) {
            if (true === \array_key_exists($filter, self::AVAILABLE_COLUMN_FILTERS)) {
                if (self::AVAILABLE_COLUMN_FILTERS[$filter]['source'] === 'column') {
                    $query .= "AND $filter = :$filter ";
                } elseif (self::AVAILABLE_COLUMN_FILTERS[$filter]['source'] === 'alias') {
                    $query .= 'AND ' . self::AVAILABLE_COLUMN_FILTERS[$filter]['columnName'] . " = :$filter ";
                } else {
                    continue;
                }

                $queryParams[$filter] = $value;
            }
        }

        // Filter on keywords
        if (\array_key_exists('keywords', $params)) {
            $query .= "AND (title LIKE :request ";
            $query .= "OR summary LIKE :request ";
            $query .= "OR title LIKE :request) ";

            $queryParams['request'] = '%' . $params['keywords'] . '%';
        }

        // Counting result
        $limit = 10;
        $page = 1;

        if (\array_key_exists('limit', $params)) {
            $limit = (int)$params['limit'];
        }

        $countQuery = 'SELECT COUNT(*) as count FROM posts ' . $query;
        $totalResultCount = $this->getEntityManager()->getConnection()->executeQuery($countQuery, $queryParams)->fetchAssociative()['count'];
        $totalPageCount = (int)ceil($totalResultCount/$limit);

        // Now: ordering
        $this->addOrderForFiltering($query, $params);

        // Pagination
        $query .= "LIMIT $limit ";

        if (\array_key_exists('page', $params)) {
            $page = (int)$params['page'];
            $page = ($page < 1) ? 1 : $page;
            $offset = ($page * $limit) - $limit;
            $query .= "OFFSET $offset ";
        }

        // Result
        $result = $this->getEntityManager()->getConnection()->executeQuery($this->getMainSelectQuery() . $query, $queryParams);
        $posts = [];

        while($post = $result->fetchAssociative()) {
            $posts[] = $this->formatPostForResponse($post);
        }

        return [
            'resultCount' => \count($posts),
            'totalResultCount' => $totalResultCount,
            'page' => $page,
            'totalPageCount' => $totalPageCount,
            'posts' => $posts,
        ];
    }

    private function getMainSelectQuery(): string
    {
        return 'SELECT id, title, summary, created_at, updated_at, published_at, slug, online, top_post, language, '
            . 'obsolete, category_id, author_id, content FROM posts ';
    }

    private function formatPostForResponse(array $result): array
    {
        // Date to the proper format
        $result['created_at'] = (new \DateTime($result['created_at']))->format(\DateTimeInterface::ATOM);
        $result['updated_at'] = (new \DateTime($result['updated_at']))->format(\DateTimeInterface::ATOM);
        $result['published_at'] =  $result['published_at'] === null ? null : (new \DateTime($result['published_at']))->format(\DateTimeInterface::ATOM);

        // Respect API conventions with lower camelCase
        $result['createdAt'] = $result['created_at'];
        unset($result['created_at']);
        $result['updatedAt'] = $result['updated_at'];
        unset($result['updated_at']);
        $result['publishedAt'] = $result['published_at'];
        unset($result['published_at']);
        $result['topPost'] = $result['top_post'];
        unset($result['top_post']);
        $result['categoryId'] = $result['category_id'];
        unset($result['category_id']);
        $result['authorId'] = $result['author_id'];
        unset($result['author_id']);

        return $result;
    }

    private function addOrderForFiltering(string &$query, $params): void
    {
        if (true === \array_key_exists('orderByField', $params)
            && true === \is_array($params['orderByField'])
        ) {
            foreach ($params['orderByField'] as $column => $order) {
                $order = strtoupper($order);

                if (true === \array_key_exists($column, self::AVAILABLE_COLUMN_FILTERS)
                    && true === \in_array($order, ['ASC', 'DESC'])
                ) {
                    if (self::AVAILABLE_COLUMN_FILTERS[$column]['source'] === 'alias') {
                        $column = self::AVAILABLE_COLUMN_FILTERS[$column]['columnName'];
                    }

                    $query .= "ORDER BY $column $order ";
                }
            }
        }
    }
}
