<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WebhookRepository")
 * @ORM\Table(name="webhooks")
 */
class Webhook
{
    private const RESOURCE_TYPE_CONFIGURATION = 'configuration';
    private const RESOURCE_TYPE_CATEGORY = 'category';
    private const RESOURCE_TYPE_POST = 'post';

    private const RESOURCE_TYPES = [
        self::RESOURCE_TYPE_CONFIGURATION,
        self::RESOURCE_TYPE_CATEGORY,
        self::RESOURCE_TYPE_POST,
    ];

    private const RESOURCE_ACTION_CREATION = 'created';
    private const RESOURCE_ACTION_EDITION = 'edited';
    private const RESOURCE_ACTION_DELETION = 'deleted';

    private const RESOURCE_ACTIONS = [
        self::RESOURCE_ACTION_CREATION,
        self::RESOURCE_ACTION_EDITION,
        self::RESOURCE_ACTION_DELETION,
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private ?string $resourceType;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private ?int $resourceId;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private ?string $action;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private ?\DateTime $creationDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTime $processedDate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getResourceType(): ?string
    {
        return $this->resourceType;
    }

    public function setResourceType(?string $resourceType): self
    {
        if (false === in_array($resourceType, self::RESOURCE_TYPES)) {
            throw new \LogicException('Unknown resource type: ' . $resourceType);
        }

        $this->resourceType = $resourceType;

        return $this;
    }

    public function getResourceId(): ?int
    {
        return $this->resourceId;
    }

    public function setResourceId(?int $resourceId): self
    {
        $this->resourceId = $resourceId;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): self
    {
        $this->action = $action;

        if (false === in_array($action, self::RESOURCE_ACTIONS)) {
            throw new \LogicException('Unknown resource action: ' . $action);
        }

        return $this;
    }

    public function getCreationDate(): ?\DateTime
    {
        return $this->creationDate;
    }

    public function setCreationDate(?\DateTime $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getProcessedDate(): ?\DateTime
    {
        return $this->processedDate;
    }

    public function setProcessedDate(?\DateTime $processedDate): self
    {
        $this->processedDate = $processedDate;

        return $this;
    }

}
