<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Webhook;
use App\Event\ResourceEvent;
use App\Exception\Category\CategoryNotEmptyException;
use App\Repository\Traits\ApiFiltersTools;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CategoryRepository extends ServiceEntityRepository implements ApiSimpleFilterResultInterface, ApiMultipleFiltersResultInterface
{
    use ApiFiltersTools;

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

    /**
     * Use for back-end only.
     *
     * @return \App\Entity\Category[]
     */
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

    public function getByMultipleApiFilters(array $params): array
    {
        [$query, $queryParams] = $this->initQueryWithMultipleFilters($params);

        return $this->executeQueryWithMultipleFilters('categories', $params, $query, $queryParams);
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

    private function formatForApiResponse(array $result): array
    {
        $result['createdAt'] = (new \DateTime($result['created_at']))->format(\DateTimeInterface::ATOM);
        $result['updatedAt'] = (new \DateTime($result['created_at']))->format(\DateTimeInterface::ATOM);

        unset($result['created_at']);
        unset($result['updated_at']);

        return $result;
    }
}
