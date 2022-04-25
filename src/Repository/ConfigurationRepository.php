<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

namespace App\Repository;

use App\Entity\Configuration;
use App\Entity\Webhook;
use App\Event\ResourceEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This repository only allows to load all the configuration at once.
 * So far we only allow to create one configuration data set.
 */
class ConfigurationRepository
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function get(): Configuration
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('m');
        $qb->from(Configuration::class, 'm');

        return $qb->getQuery()->getSingleResult();
    }

    // Remember to flush after that
    public function save(Configuration $configuration): void
    {
        $this->entityManager->persist($configuration);
        $this->eventDispatcher->dispatch(new ResourceEvent($configuration, Webhook::RESOURCE_ACTION_EDITION), ResourceEvent::NAME);
    }
}
