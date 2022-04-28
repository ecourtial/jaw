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
 * @ORM\HasLifecycleCallbacks()
 */
class Webhook
{
    private const RESOURCE_TYPE_CONFIGURATION = 'configuration';
    private const RESOURCE_TYPE_CATEGORY = 'category';
    private const RESOURCE_TYPE_POST = 'post';
    private const RESOURCE_TYPE_USER = 'user';

    private const RESOURCE_TYPES = [
        self::RESOURCE_TYPE_CONFIGURATION,
        self::RESOURCE_TYPE_CATEGORY,
        self::RESOURCE_TYPE_POST,
        self::RESOURCE_TYPE_USER,
    ];

    public const RESOURCE_ACTION_CREATION = 'created';
    public const RESOURCE_ACTION_EDITION = 'edited';
    public const RESOURCE_ACTION_DELETION = 'deleted';

    private const RESOURCE_ACTIONS = [
        self::RESOURCE_ACTION_CREATION,
        self::RESOURCE_ACTION_EDITION,
        self::RESOURCE_ACTION_DELETION,
    ];

    public const MAX_ATTEMPT_COUNT = 5;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private ?string $resourceType = null;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private ?int $resourceId = null;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private ?string $action = null;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private ?\DateTime $creationDate = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTime $processedDate = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTime $lastAttempt = null;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $attemptCount = 0;

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

    /** @ORM\PrePersist () */
    public function setCreationDate(): void
    {
        $this->creationDate = new \DateTime();
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

    public function getLastAttempt(): ?\DateTime
    {
        return $this->lastAttempt;
    }

    /** @ORM\PreUpdate() */
    public function setLastAttempt(): void
    {
        $this->lastAttempt = new \DateTime();
    }

    public function getAttemptCount(): int
    {
        return $this->attemptCount;
    }

    // I did not set any PreUpdate annotation because I needed something to edit, so I could update the object.
    public function incrementAttemptCount(): self
    {
        $this->attemptCount++;

        return $this;
    }
}
