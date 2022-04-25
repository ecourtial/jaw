<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Webhook;
use App\Event\ResourceEvent;
use App\Exception\Category\CategoryNotEmptyException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly EventDispatcherInterface $eventDispatcher,)
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
        $this->eventDispatcher->dispatch(new ResourceEvent($category, $actionType), ResourceEvent::NAME);
    }

    public function delete(Category $category): void
    {
        if ($category->getPosts()->isEmpty()) {
            $this->getEntityManager()->remove($category);
            $this->eventDispatcher->dispatch(new ResourceEvent($category, Webhook::RESOURCE_ACTION_DELETION), ResourceEvent::NAME);
        } else {
            throw new CategoryNotEmptyException();
        }
    }
}
