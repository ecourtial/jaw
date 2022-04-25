<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Webhook;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ResourceEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            ResourceEvent::NAME => 'createWebhook',
        ];
    }

    public function createWebhook(ResourceEvent $event)
    {
        $webhook = (new Webhook())
            ->setResourceId($event->getResource()->getId())
            ->setResourceType($event->getResource()->getResourceType())
            ->setAction($event->getActionType());

        $this->entityManager->persist($webhook);
    }
}
