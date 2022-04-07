<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

namespace App\Repository;

use App\Entity\Configuration;
use Doctrine\ORM\EntityManagerInterface;

/**
 * This repository only allows to load all the configuration at once.
 * So far we only allow to create one configuration data.
 */
class ConfigurationRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function get(): Configuration
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('m');
        $qb->from(Configuration::class, 'm');

        return $qb->getQuery()->getSingleResult();
    }

    public function save(Configuration $configuration): void
    {
        $this->entityManager->persist($configuration);
        $this->entityManager->flush();
    }
}
