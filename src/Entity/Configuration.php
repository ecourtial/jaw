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
    #[Assert\NotBlank, Assert\Length(min: 10, max: 50)]
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
    #[Assert\Length(min: 10, max: 50)]
    private ?string $linkedinUsername;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    #[Assert\Length(min: 10, max: 50)]
    private ?string $githubUsername;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    #[Assert\Length(min: 10, max: 50)]
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

    public function setBlogTitle(?string $blogTitle): void
    {
        $this->blogTitle = $blogTitle;
    }

    public function getBlogDescription(): ?string
    {
        return $this->blogDescription;
    }

    public function setBlogDescription(?string $blogDescription): void
    {
        $this->blogDescription = $blogDescription;
    }

    public function getCopyrightMessage(): ?string
    {
        return $this->copyrightMessage;
    }

    public function setCopyrightMessage(?string $copyrightMessage): void
    {
        $this->copyrightMessage = $copyrightMessage;
    }

    public function getCopyrightExtraMessage(): ?string
    {
        return $this->copyrightExtraMessage;
    }

    public function setCopyrightExtraMessage(?string $copyrightExtraMessage): void
    {
        $this->copyrightExtraMessage = $copyrightExtraMessage;
    }

    public function getLinkedinUsername(): ?string
    {
        return $this->linkedinUsername;
    }

    public function setLinkedinUsername(?string $linkedinUsername): void
    {
        $this->linkedinUsername = $linkedinUsername;
    }

    public function getGithubUsername(): ?string
    {
        return $this->githubUsername;
    }

    public function setGithubUsername(?string $githubUsername): void
    {
        $this->githubUsername = $githubUsername;
    }

    public function getGoogleAnalyticsId(): ?string
    {
        return $this->googleAnalyticsId;
    }

    public function setGoogleAnalyticsId(?string $googleAnalyticsId): void
    {
        $this->googleAnalyticsId = $googleAnalyticsId;
    }
}
