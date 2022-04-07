<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

namespace App\Repository;

use App\Entity\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * This repository only allows to load all the configuration at once.
 * So far we only allow to create one configuration data.
 */
class ConfigurationRepository
{
    private EntityRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Configuration::class);
        $this->entityManager = $entityManager;
    }

    public function get(): Configuration
    {
        return $this->repository->find(1);
    }

    public function save(Configuration $configuration): void
    {
        $this->entityManager->persist($configuration);
        $this->entityManager->flush();
    }
}
