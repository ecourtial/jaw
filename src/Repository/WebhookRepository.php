<?php

namespace App\Repository;

use App\Entity\Webhook;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class WebhookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Webhook::class);
    }

    // We do not add any new entry for the given resource if there is still any pending webhook
    public function create(Webhook $webhook): bool
    {
        if ($this->getUnprocessedWebhookExists($webhook)) {
            return false;
        }

        $this->save($webhook);

        return true;
    }

    public function getUnprocessedWebhookExists(Webhook $webhook): ?Webhook
    {
        $q = $this->createQueryBuilder('p')
            ->where('p.resourceId = :resourceId')
            ->andWhere('p.resourceType = :resourceType')
            ->andWhere('p.processedDate IS NULL')
            ->andWhere('p.attemptCount < :maxAttemptCount')
            ->andWhere('p.action = :actionType')
            ->setParameter('resourceId', $webhook->getResourceId())
            ->setParameter('resourceType', $webhook->getResourceType())
            ->setParameter('maxAttemptCount', Webhook::MAX_ATTEMPT_COUNT)
            ->setParameter('actionType', $webhook->getAction())
            ->setMaxResults(1)
            ->getQuery();

        if ($q->getResult()) {
            return $q->getResult()[0];
        }

        return null;
    }

    // Be careful when using this method directly. If you have any doubt, use create() instead.
    public function save(Webhook $webhook): void
    {
        $this->getEntityManager()->persist($webhook);
        $this->getEntityManager()->flush();
    }
}
