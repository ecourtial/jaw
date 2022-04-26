<?php

namespace App\Tests\Unit\EventSubscriber;

use App\Entity\Category;
use App\Entity\Webhook;
use App\Event\ResourceEvent;
use App\EventSubscriber\ResourceEventSubscriber;
use App\Repository\WebhookRepository;
use PHPUnit\Framework\TestCase;

class ResourceEventSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        static::assertEquals([ResourceEvent::NAME => 'createWebhook'], ResourceEventSubscriber::getSubscribedEvents());
    }

    public function testCreateWebHook(): void
    {
        $repository = $this->createMock(WebhookRepository::class);
        $subscriber = new ResourceEventSubscriber($repository);

        $webhook = (new Webhook())
            ->setAction(Webhook::RESOURCE_ACTION_CREATION)
            ->setResourceId(2)
            ->setResourceType('category');

        $resourceEvent = new ResourceEvent(
            (new Category())->setId(2),
            Webhook::RESOURCE_ACTION_CREATION
        );

        $repository->expects(static::once())->method('create')->with($webhook);

        $subscriber->createWebhook($resourceEvent);
    }
}
