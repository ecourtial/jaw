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
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class ConfigurationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ConfigurationRepository $configurationRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function save(Configuration $configuration): void
    {
        $this->entityManager->beginTransaction();

        try {
            $this->configurationRepository->save($configuration);
            $this->entityManager->commit();
        } catch (\Throwable $exception) {
            $this->entityManager->rollback();
            $this->logger->log(LogLevel::ERROR, 'Impossible to save the configuration.', ['exception' => $exception]);
            throw  $exception;
        }
    }

    public function get(): Configuration
    {
        return $this->configurationRepository->get();
    }
}
