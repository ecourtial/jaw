<?php

namespace App\Tests\Unit\EventSubscriber;

use App\Entity\Category;
use App\Entity\Configuration;
use App\Entity\Webhook;
use App\Event\ResourceEvent;
use App\EventSubscriber\ResourceEventSubscriber;
use App\Repository\ConfigurationRepository;
use App\Repository\WebhookRepository;
use PHPUnit\Framework\TestCase;

class ResourceEventSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        static::assertEquals([ResourceEvent::NAME => 'createWebhook'], ResourceEventSubscriber::getSubscribedEvents());
    }

    public function testCannotCreateWebhooksBecauseFeatureIsDisabled(): void
    {
        $webhooksRepository = $this->createMock(WebhookRepository::class);
        $configurationRepository = $this->createMock(ConfigurationRepository::class);

        $configurationRepository->expects(static::once())->method('get')->willReturn(new Configuration());

        $subscriber = new ResourceEventSubscriber($webhooksRepository, $configurationRepository);

        $resourceEvent = new ResourceEvent(
            (new Category())->setId(2),
            Webhook::RESOURCE_ACTION_CREATION
        );

        $webhooksRepository->expects(static::never())->method('create');

        $subscriber->createWebhook($resourceEvent);
    }

    public function testCreateWebHook(): void
    {
        $webhooksRepository = $this->createMock(WebhookRepository::class);
        $configurationRepository = $this->createMock(ConfigurationRepository::class);

        $configurationRepository->expects(static::once())->method('get')->willReturn(
            (new Configuration())->setWebhooksEnabled(true)
        );

        $subscriber = new ResourceEventSubscriber($webhooksRepository, $configurationRepository);

        $webhook = (new Webhook())
            ->setAction(Webhook::RESOURCE_ACTION_CREATION)
            ->setResourceId(2)
            ->setResourceType('category');

        $resourceEvent = new ResourceEvent(
            (new Category())->setId(2),
            Webhook::RESOURCE_ACTION_CREATION
        );

        $webhooksRepository->expects(static::once())->method('create')->with($webhook);

        $subscriber->createWebhook($resourceEvent);
    }
}
