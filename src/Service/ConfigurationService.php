<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Service;

use App\Entity\Configuration;
use App\Entity\Webhook;
use App\Event\ResourceEvent;
use App\Repository\ConfigurationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConfigurationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ConfigurationRepository $configurationRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    // Remember to flush after that
    public function save(Configuration $configuration): void
    {
        $this->entityManager->persist($configuration);
        $this->eventDispatcher->dispatch(new ResourceEvent($configuration, Webhook::RESOURCE_ACTION_EDITION), ResourceEvent::NAME);
    }

    public function get(): Configuration
    {
        return $this->configurationRepository->get();
    }
}
