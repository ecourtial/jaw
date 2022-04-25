<?php

namespace App\Tests\Unit\Repository;

use App\Repository\ConfigurationRepository;
use App\Entity\Configuration;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConfigurationRepositoryTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $eventDispatcher;

    public function setup(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
    }

    public function testGet(): void
    {
        $configuration = (new Configuration())->setLinkedinUsername('LinkedinUsername');

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $this->entityManager->expects(static::once())->method('createQueryBuilder')->willReturn($queryBuilder);

        $queryBuilder->expects(static::once())->method('select')->with('m');
        $queryBuilder->expects(static::once())->method('from')->with(Configuration::class, 'm');

        $query = $this->createMock(AbstractQuery::class);
        $query->expects(static::once())->method('getSingleResult')->willReturn($configuration);

        $queryBuilder->expects(static::once())->method('getQuery')->willReturn($query);

        $repo = new ConfigurationRepository($this->entityManager, $this->eventDispatcher);

        static::assertEquals($configuration, $repo->get());
    }
}
