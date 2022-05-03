<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Webhook;
use App\Event\ResourceEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $user): void
    {
        $actionType = ($user->getId() === null) ? Webhook::RESOURCE_ACTION_CREATION : Webhook::RESOURCE_ACTION_EDITION;

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
        $this->eventDispatcher->dispatch(new ResourceEvent($user, $actionType), ResourceEvent::NAME);
    }

    public function findByUsername(string $username): User
    {
        $result = $this->findBy(['username' => $username]);

        if (!$result) {
            throw new NoResultException();
        }

        return $result[0];
    }
}
