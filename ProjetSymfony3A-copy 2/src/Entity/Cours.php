<?php

namespace App\Entity;

use App\Repository\CoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CoursRepository::class)]
#[ORM\Table(name: 'cours')]
#[ORM\HasLifecycleCallbacks]
class Cours
{
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REFUSED = 'refused';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Course title is required')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Course title must be at least 3 characters',
        maxMessage: 'Course title cannot exceed 255 characters'
    )]
    #[Assert\Regex(
        pattern: '/^[A-Z]/',
        message: 'Course title must start with an uppercase letter (A-Z)'
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Course description is required')]
    #[Assert\Length(
        min: 10,
        minMessage: 'Course description must be at least 10 characters',
        max: 5000,
        maxMessage: 'Course description cannot exceed 5000 characters'
    )]
    #[Assert\Regex(
        pattern: '/^[A-Z]/',
        message: 'Course description must start with an uppercase letter (A-Z)'
    )]
    private ?string $description = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\NotBlank(message: 'Course category is required')]
    private ?string $category = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Course content is required')]
    #[Assert\Length(
        min: 50,
        minMessage: 'Course content must be at least 50 characters',
        max: 10000,
        maxMessage: 'Course content cannot exceed 10000 characters'
    )]
    #[Assert\Regex(
        pattern: '/^[A-Z]/',
        message: 'Course content must start with an uppercase letter (A-Z)'
    )]
    private ?string $content = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Course level is required')]
    #[Assert\Choice(choices: ['beginner', 'intermediate', 'advanced'], message: 'Invalid course level')]
    private ?string $level = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $courseFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $courseVideo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $courseImage = null;

    #[ORM\Column(length: 50)]
    private ?string $status = self::STATUS_PENDING;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $creator = null;

    /**
     * @var Collection<int, Chapitre>
     */
    #[ORM\OneToMany(targetEntity: Chapitre::class, mappedBy: 'cours', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $chapitres;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->status = self::STATUS_PENDING;
        $this->chapitres = new ArrayCollection();
    }

    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): static
    {
        $this->level = $level;
        return $this;
    }

    public function getCourseFile(): ?string
    {
        return $this->courseFile;
    }

    public function setCourseFile(?string $courseFile): static
    {
        $this->courseFile = $courseFile;
        return $this;
    }

    public function getCourseVideo(): ?string
    {
        return $this->courseVideo;
    }

    public function setCourseVideo(?string $courseVideo): static
    {
        $this->courseVideo = $courseVideo;
        return $this;
    }

    public function getCourseImage(): ?string
    {
        return $this->courseImage;
    }

    public function setCourseImage(?string $courseImage): static
    {
        $this->courseImage = $courseImage;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): static
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * @return Collection<int, Chapitre>
     */
    public function getChapitres(): Collection
    {
        return $this->chapitres;
    }

    public function addChapitre(Chapitre $chapitre): static
    {
        if (!$this->chapitres->contains($chapitre)) {
            $this->chapitres->add($chapitre);
            $chapitre->setCours($this);
        }
        return $this;
    }

    public function removeChapitre(Chapitre $chapitre): static
    {
        if ($this->chapitres->removeElement($chapitre)) {
            if ($chapitre->getCours() === $this) {
                $chapitre->setCours(null);
            }
        }
        return $this;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isRefused(): bool
    {
        return $this->status === self::STATUS_REFUSED;
    }

    public function approve(): void
    {
        $this->status = self::STATUS_APPROVED;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function refuse(): void
    {
        $this->status = self::STATUS_REFUSED;
        $this->updatedAt = new \DateTimeImmutable();
    }
}
