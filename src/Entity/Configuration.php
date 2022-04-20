<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="configuration")
 */
class Configuration
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    #[Assert\NotBlank, Assert\Length(min: 5, max: 50)]
    private ?string $blogTitle;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    #[Assert\NotBlank, Assert\Length(min: 10, max: 500)]
    private ?string $blogDescription;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    #[Assert\NotBlank, Assert\Length(min: 10, max: 50)]
    private ?string $copyrightMessage;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    #[Assert\Length(min: 10, max: 50)]
    private ?string $copyrightExtraMessage;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    #[Assert\Length(min: 5, max: 50)]
    private ?string $linkedinUsername;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    #[Assert\Length(min: 5, max: 50)]
    private ?string $githubUsername;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    #[Assert\Length(min: 5, max: 50)]
    private ?string $googleAnalyticsId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getBlogTitle(): ?string
    {
        return $this->blogTitle;
    }

    public function setBlogTitle(?string $blogTitle): self
    {
        $this->blogTitle = $blogTitle;

        return $this;
    }

    public function getBlogDescription(): ?string
    {
        return $this->blogDescription;
    }

    public function setBlogDescription(?string $blogDescription): self
    {
        $this->blogDescription = $blogDescription;

        return $this;
    }

    public function getCopyrightMessage(): ?string
    {
        return $this->copyrightMessage;
    }

    public function setCopyrightMessage(?string $copyrightMessage): self
    {
        $this->copyrightMessage = $copyrightMessage;

        return $this;
    }

    public function getCopyrightExtraMessage(): ?string
    {
        return $this->copyrightExtraMessage;
    }

    public function setCopyrightExtraMessage(?string $copyrightExtraMessage): self
    {
        $this->copyrightExtraMessage = $copyrightExtraMessage;

        return $this;
    }

    public function getLinkedinUsername(): ?string
    {
        return $this->linkedinUsername;
    }

    public function setLinkedinUsername(?string $linkedinUsername): self
    {
        $this->linkedinUsername = $linkedinUsername;

        return $this;
    }

    public function getGithubUsername(): ?string
    {
        return $this->githubUsername;
    }

    public function setGithubUsername(?string $githubUsername): self
    {
        $this->githubUsername = $githubUsername;

        return $this;
    }

    public function getGoogleAnalyticsId(): ?string
    {
        return $this->googleAnalyticsId;
    }

    public function setGoogleAnalyticsId(?string $googleAnalyticsId): self
    {
        $this->googleAnalyticsId = $googleAnalyticsId;

        return $this;
    }
}
