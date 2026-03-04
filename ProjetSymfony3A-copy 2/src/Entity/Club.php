<?php

namespace App\Entity;

use App\Repository\ClubRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClubRepository::class)]
#[ORM\Table(name: 'club')]
class Club
{
    const STATUS_PENDING = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_REJECTED = 'REJECTED';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom du club est obligatoire.')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le nom du club doit comporter au moins {{ limit }} caractères.',
        maxMessage: 'Le nom du club ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\NotBlank(message: 'La description du club est obligatoire.')]
    #[Assert\Length(
        min: 10,
        minMessage: 'La description doit comporter au moins {{ limit }} caractères.'
    )]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    private ?string $status = self::STATUS_PENDING;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $creator = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'club', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $events;

    /**
     * @var Collection<int, JoinRequest>
     */
    #[ORM\OneToMany(targetEntity: JoinRequest::class, mappedBy: 'club', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $joinRequests;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'club_members')]
    private Collection $members;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = self::STATUS_PENDING;
        $this->events = new ArrayCollection();
        $this->joinRequests = new ArrayCollection();
        $this->members = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
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

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function approve(): static
    {
        $this->status = self::STATUS_APPROVED;
        return $this;
    }

    public function reject(): static
    {
        $this->status = self::STATUS_REJECTED;
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

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
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
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setClub($this);
        }
        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            if ($event->getClub() === $this) {
                $event->setClub(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, JoinRequest>
     */
    public function getJoinRequests(): Collection
    {
        return $this->joinRequests;
    }

    public function addJoinRequest(JoinRequest $joinRequest): static
    {
        if (!$this->joinRequests->contains($joinRequest)) {
            $this->joinRequests->add($joinRequest);
            $joinRequest->setClub($this);
        }
        return $this;
    }

    public function removeJoinRequest(JoinRequest $joinRequest): static
    {
        if ($this->joinRequests->removeElement($joinRequest)) {
            if ($joinRequest->getClub() === $this) {
                $joinRequest->setClub(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(User $member): static
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
        }
        return $this;
    }

    public function removeMember(User $member): static
    {
        $this->members->removeElement($member);
        return $this;
    }

    public function isMember(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        return $this->members->contains($user);
    }
}

