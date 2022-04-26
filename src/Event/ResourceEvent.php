<?php

namespace App\Event;

use App\Entity\ResourceInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The resource.updated event is dispatched each time a resource (category, post, configuration) is edited.
 */
class ResourceEvent extends Event
{
    public const NAME = 'resource.updated';

    public function __construct(private readonly ResourceInterface $resource, private readonly string $actionType)
    {
    }

    public function getResource(): ResourceInterface
    {
        return $this->resource;
    }

    public function getActionType(): string
    {
        return $this->actionType;
    }
}
