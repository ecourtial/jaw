<?php

declare(strict_types=1);

namespace App\Entity;

trait DatedResourceTrait
{
    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private \DateTime $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private \DateTime $updatedAt;

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist()
     */
    public function setCreatedAt(): void
    {
        $this->createdAt = new \DateTime();
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PreUpdate()
     * @ORM\PrePersist()
     */
    public function setUpdatedAt(): void
    {
        $this->updatedAt  = new \DateTime();
    }
}
