<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Service;

use App\Entity\Configuration;
use App\Repository\ConfigurationRepository;
use Doctrine\ORM\EntityManagerInterface;

class ConfigurationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ConfigurationRepository $configurationRepository,
    ) {
    }

    // Remember to flush after that
    public function save(Configuration $configuration): void
    {
        $this->entityManager->persist($configuration);
        // Dispatch here
    }

    public function get(): Configuration
    {
        return $this->configurationRepository->get();
    }
}
