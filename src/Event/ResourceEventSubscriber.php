<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Webhook;
use App\Repository\WebhookRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ResourceEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly WebhookRepository $webhookRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ResourceEvent::NAME => 'createWebhook',
        ];
    }

    public function createWebhook(ResourceEvent $event): void
    {
        $webhook = (new Webhook())
            ->setResourceId($event->getResource()->getId())
            ->setResourceType($event->getResource()->getResourceType())
            ->setAction($event->getActionType());

        $this->webhookRepository->create($webhook);
    }
}
