<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Webhook;
use App\Event\ResourceEvent;
use App\Exception\Category\CategoryNotEmptyException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CategoryRepository extends ServiceEntityRepository implements ApiFilterableResultInterface
{
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

    public function getByApiFilter(string $filter, string|int $param): array
    {
        
    }
}
