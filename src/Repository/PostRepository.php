<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\Webhook;
use App\Event\ResourceEvent;
use App\Repository\Traits\ApiFiltersTools;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostRepository extends ServiceEntityRepository implements ApiSimpleFilterResultInterface, ApiMultipleFiltersResultInterface
{
    use ApiFiltersTools;

    private const AVAILABLE_COLUMN_FILTERS = [
        'id' => ['source' => 'column',],
        'author' => ['source' => 'alias', 'columnName' => 'author_id', ],
        'category' => ['source' => 'alias', 'columnName' => 'category_id',],
        'title' => ['source' => 'column',],
        'slug' => ['source' => 'column',],
        'publishedAt' => ['source' => 'alias', 'columnName' => 'published_at',],
        'language' => ['source' => 'column', ],
        'online' => ['source' => 'column', ],
        'topPost' => ['source' => 'column', ],
        'obsolete' => ['source' => 'column', ],
        'createdAt' => ['source' => 'alias', 'columnName' => 'created_at',],
        'updatedAt' => ['source' => 'alias', 'columnName' => 'updated_at',],
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
     * @return array<string, mixed>
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
            'results' => [],
        ];

        foreach ($result as $entry) {
            $processedResult['results'][] = [
                'id' => $entry['id'],
                'title' => $entry['title'],
                'createdAt' => $entry['createdAt'],
                'categoryId' => $entry['categoryId'],
                'categoryTitle' => $entry['categoryTitle'],
            ];
        }

        return $processedResult;
    }

    // Unlike the 'getByUniqueApiFilter'method, unknown filters are ignored.
    /** @return array<string, mixed> */
    public function getByMultipleApiFilters(array $params): array
    {
        [$query, $queryParams] = $this->initQueryWithMultipleFilters($params);
        $this->addKeywordsFilters($query, $queryParams, $params);

        return $this->executeQueryWithMultipleFilters('posts', $params, $query, $queryParams);
    }

    private function getMainSelectQuery(): string
    {
        return 'SELECT id, title, summary, created_at, updated_at, published_at, slug, online, top_post, language, '
            . 'obsolete, category_id, author_id, content FROM posts WHERE id IS NOT NULL ';
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function formatForApiResponse(array $result): array
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

        // Set status to bool format
        $result['online'] = (bool)$result['online'];
        $result['obsolete'] = (bool)$result['obsolete'];
        $result['topPost'] = (bool)$result['topPost'];


        return $result;
    }

    /**
     * @param array<string, mixed> $queryParams
     * @param array<string, mixed> $params
     */
    private function addKeywordsFilters(string &$query, array &$queryParams, array $params): void
    {
        // Filter on keywords
        if (\array_key_exists('keywords', $params)) {
            $query .= "AND (title LIKE :request ";
            $query .= "OR summary LIKE :request ";
            $query .= "OR title LIKE :request) ";

            $queryParams['request'] = '%' . $params['keywords'] . '%';
        }
    }
}
