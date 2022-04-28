<?php

declare(strict_types=1);

namespace App\Entity;

interface DatedResourceInterface
{
    public function getId(): ?int;
    public function getResourceType(): string;
    public function getCreatedAt(): ?\DateTime;
    public function setCreatedAt(): void; // No param
    public function getUpdatedAt(): ?\DateTime;
    public function setUpdatedAt(): void; // No param
}
