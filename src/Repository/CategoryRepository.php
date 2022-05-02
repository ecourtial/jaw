<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Webhook;
use App\Event\ResourceEvent;
use App\Exception\Category\CategoryNotEmptyException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CategoryRepository extends ServiceEntityRepository implements ApiSimpleFilterResultInterface, ApiMultipleFiltersResultInterface
{
    private const AVAILABLE_COLUMN_FILTERS = [
        'id' => ['source' => 'column',],
        'title' => ['source' => 'column',],
        'summary' => ['source' => 'column',],
        'createdAt' => ['source' => 'alias', 'columnName' => 'created_at',],
        'updatedAt' => ['source' => 'alias', 'columnName' => 'updated_at',],
        'slug' => ['source' => 'column',],
        'postCount' => ['source' => 'computed'],
    ];

    public function __construct(ManagerRegistry $registry, private readonly EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($registry, Category::class);
    }

    /** @return \App\Entity\Category[] */
    public function listAll(): array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('m');
        $qb->from(Category::class, 'm');
        $qb->orderBy('m.title');
        $result = $qb->getQuery()->getResult();

        if (empty($result)) {
            $result = [];
        }

        return $result;
    }

    public function save(Category $category): void
    {
        $actionType = ($category->getId() === null) ? Webhook::RESOURCE_ACTION_CREATION : Webhook::RESOURCE_ACTION_EDITION;

        $this->getEntityManager()->persist($category);
        $this->getEntityManager()->flush();
        $this->eventDispatcher->dispatch(new ResourceEvent($category, $actionType), ResourceEvent::NAME);
    }

    public function delete(Category $category): void
    {
        if ($category->getPosts()->isEmpty()) {
            $this->eventDispatcher->dispatch(new ResourceEvent($category, Webhook::RESOURCE_ACTION_DELETION), ResourceEvent::NAME);
            $this->getEntityManager()->remove($category);
            $this->getEntityManager()->flush();

            return;
        }

        throw new CategoryNotEmptyException();
    }

    /** @return string[] */
    public function getByUniqueApiFilter(string $filter, string|int $param): array
    {
        if ($filter === 'slug' || $filter === 'id') {
            $result = $this->getByMultipleApiFilters([$filter => $param]);
            if (0 === $result['resultCount']) {
                throw new NoResultException();
            }

            return $result['categories'][0];
        } else {
            throw new \LogicException('Unsupported filter: ' . $filter);
        }
    }

    public function getByMultipleApiFilters(array $params): array
    {
        $queryParams = [];
        // Creation of the second par of the query (the conditions)
        $query = '';

        // First, basic filters on value
        foreach ($params as $filter => $value) {
            if (true === \array_key_exists($filter, self::AVAILABLE_COLUMN_FILTERS)) {
                if (self::AVAILABLE_COLUMN_FILTERS[$filter]['source'] === 'column'
                    || self::AVAILABLE_COLUMN_FILTERS[$filter]['source'] === 'computed'
                ) {
                    $query .= "AND $filter = :$filter ";
                } elseif (self::AVAILABLE_COLUMN_FILTERS[$filter]['source'] === 'alias') {
                    // @phpstan-ignore-next-line
                    $query .= 'AND ' . self::AVAILABLE_COLUMN_FILTERS[$filter]['columnName'] . " = :$filter ";
                } else {
                    continue;
                }

                $queryParams[$filter] = $value;
            }
        }

        // Counting result
        $limit = 10;
        $page = 1;

        if (\array_key_exists('limit', $params)) {
            $limit = (int)$params['limit'];
        }

        $countQuery = 'SELECT COUNT(*) as count FROM categories WHERE id IS NOT NULL ' . $query;
        // @phpstan-ignore-next-line
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

        $totalPageCount = ($totalResultCount === 0) ? 1 : $totalPageCount;

        // Result
        $result = $this->getEntityManager()->getConnection()->executeQuery($this->getMainSelectQuery() . $query, $queryParams);
        $categories = [];

        while ($category = $result->fetchAssociative()) {
            $categories[] = $this->formatCategoryForResponse($category);
        }

        return [
            'resultCount' => \count($categories),
            'totalResultCount' => $totalResultCount,
            'page' => $page,
            'totalPageCount' => $totalPageCount,
            'categories' => $categories,
        ];
    }

    private function getMainSelectQuery(): string
    {
        return 'SELECT c.id, c.title, c.summary, c.created_at, c.updated_at, c.slug, p.postCount AS postCount '
            . 'FROM categories AS c, '
            . '(SELECT COUNT(*) AS postCount, categories.id as categId '
            . '   FROM posts, categories '
            . '   WHERE posts.category_id = categories.id '
            . '   GROUP BY categories.id) AS p '
            . 'WHERE c.id = p.categId ';
    }

    private function formatCategoryForResponse(array $result): array
    {
        $result['createdAt'] = (new \DateTime($result['created_at']))->format(\DateTimeInterface::ATOM);
        $result['updatedAt'] = (new \DateTime($result['created_at']))->format(\DateTimeInterface::ATOM);

        unset($result['created_at']);
        unset($result['updated_at']);

        return $result;
    }

    /** @param array<string, mixed> $params */
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
                        // @phpstan-ignore-next-line
                        $column = self::AVAILABLE_COLUMN_FILTERS[$column]['columnName'];
                    }

                    $query .= "ORDER BY $column $order ";
                }
            }
        }
    }
}
