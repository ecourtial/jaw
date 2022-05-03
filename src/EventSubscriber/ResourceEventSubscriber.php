<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Webhook;
use App\Event\ResourceEvent;
use App\Repository\ConfigurationRepository;
use App\Repository\WebhookRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ResourceEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly WebhookRepository $webhookRepository,
        private readonly ConfigurationRepository $configurationRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ResourceEvent::NAME => 'createWebhook',
        ];
    }

    public function createWebhook(ResourceEvent $event): void
    {
        if ($this->configurationRepository->get()->getWebhooksEnabled()) {
            $webhook = (new Webhook())
                ->setResourceId($event->getResource()->getId())
                ->setResourceType($event->getResource()->getResourceType())
                ->setAction($event->getActionType());

            $this->webhookRepository->create($webhook);
        }
    }
}
