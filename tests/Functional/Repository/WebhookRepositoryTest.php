<?php

namespace App\Tests\Functional\Repository;

use App\Entity\Webhook;
use App\Repository\WebhookRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WebhookRepositoryTest extends KernelTestCase
{
    private WebhookRepository $webhookRepository;

    public function setup(): void
    {
        $kernel = self::bootKernel();

        $this->webhookRepository = $kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(Webhook::class);
    }

    public function testBehavior(): void
    {
        // Creation

        $webhook = (new Webhook())
            ->setResourceId(1)
            ->setResourceType('post')
            ->setAction(Webhook::RESOURCE_ACTION_CREATION);

        static::assertTrue($this->webhookRepository->create($webhook));

        // EDIT action

        $webhook2 = (new Webhook())
            ->setResourceId(1)
            ->setResourceType('post')
            ->setAction(Webhook::RESOURCE_ACTION_EDITION);

        static::assertTrue($this->webhookRepository->create($webhook2));

        // Do not override

        $webhook3 = (new Webhook())
            ->setResourceId(1)
            ->setResourceType('post')
            ->setAction(Webhook::RESOURCE_ACTION_EDITION);

        static::assertFalse($this->webhookRepository->create($webhook3));

        // Get the first unprocessed (must be the creation one)

        $firstEntry = $this->webhookRepository->findAll()[0];
        static::assertEquals($firstEntry, $this->webhookRepository->getFirstUnprocessed());

        // Reach the max count of attempt

        $webhook4 = $this->webhookRepository->getUnprocessedWebhookExists($webhook3);

        $webhook4->incrementAttemptCount();
        $this->webhookRepository->save($webhook4);

        $webhook4->incrementAttemptCount();
        $this->webhookRepository->save($webhook4);

        $webhook4->incrementAttemptCount();
        $this->webhookRepository->save($webhook4);

        $webhook4->incrementAttemptCount();
        $this->webhookRepository->save($webhook4);

        $webhook4->incrementAttemptCount();
        $this->webhookRepository->save($webhook4);

        // So, now you can override

        $webhook5 = (new Webhook())
            ->setResourceId(1)
            ->setResourceType('post')
            ->setAction(Webhook::RESOURCE_ACTION_EDITION);

        static::assertTrue($this->webhookRepository->create($webhook5));
    }
}
