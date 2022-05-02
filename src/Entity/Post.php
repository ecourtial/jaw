<?php

/**
 * @author      Eric COURTIAL <e.courtial30@gmail.com.com>
 * @license     MIT
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
 * @ORM\Table(name="posts", indexes={
 *     @ORM\Index(name="online", columns={"online"}),
 *     @ORM\Index(name="summary", columns={"summary"})
 * })
 * @UniqueEntity(fields={"slug"}, message="post.slug_must_be_unique")
 * @ORM\HasLifecycleCallbacks()
 */
class Post implements DatedResourceInterface
{
    use DatedResourceTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    #[Assert\NotBlank]
    private ?string $title = null;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private ?string $slug = null;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    #[
        Assert\NotBlank(message: 'post.blank_summary'),
        Assert\Length(max: 255)
    ]
    private ?string $summary = null;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    #[
        Assert\NotBlank(message: 'post.blank_content'),
        Assert\Length(min: 10, minMessage: 'post.too_short_content')
    ]
    private ?string $content = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?\DateTime $publishedAt = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $author = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Category $category = null;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     */
    private bool $online = false;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     */
    private bool $topPost = false;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     */
    private bool $obsolete = false;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    #[Assert\NotBlank]
    private ?string $language = null;

    public function getResourceType(): string
    {
        return 'post';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getPublishedAt(): ?\DateTime
    {
        return $this->publishedAt;
    }

    /**
     * @ORM\PreUpdate()
     * @ORM\PrePersist()
     */
    public function setPublishedAt(): self
    {
        if ($this->isOnline()) {
            $this->publishedAt = new \DateTime();
        } else {
            $this->publishedAt = null;
        }

        return $this;
    }

    // Used for migration from old blog systems.
    public function forcePublishedAtDate(\DateTime $date): self
    {
        $this->publishedAt = $date;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function isOnline(): bool
    {
        return $this->online;
    }

    public function setOnline(bool $isOnline): self
    {
        $this->online = $isOnline;

        return $this;
    }

    public function isTopPost(): bool
    {
        return $this->topPost;
    }

    public function setTopPost(bool $isTopPost): self
    {
        $this->topPost = $isTopPost;

        return $this;
    }

    public function isObsolete(): bool
    {
        return $this->obsolete;
    }

    public function setObsolete(bool $isObsolete): self
    {
        $this->obsolete = $isObsolete;

        return $this;
    }
}
