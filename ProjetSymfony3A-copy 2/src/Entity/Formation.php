<?php

namespace App\Entity;

use App\Repository\FormationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormationRepository::class)]
class Formation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column]
    private ?string $description = null;

    #[ORM\Column]
    private ?string $content = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column]
    private ?bool $isApproved = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $supportFile = null;

    #[ORM\Column]
    private bool $isArchived = false;

    #[ORM\Column(nullable: true)]
    private ?int $duration = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $level = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $creator = null;

    /**
     * @var Collection<int, Quizfor>
     */
    #[ORM\OneToMany(targetEntity: Quizfor::class, mappedBy: 'Formation', orphanRemoval: true)]
    private Collection $quizzes;

    /**
     * @var Collection<int, Ressources>
     */
    #[ORM\OneToMany(targetEntity: Ressources::class, mappedBy: 'formation', orphanRemoval: true)]
    private Collection $ressources;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'formations')]
    private Collection $users;

  

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function isApproved(): ?bool
    {
        return $this->isApproved;
    }

    public function setIsApproved(bool $isApproved): static
    {
        $this->isApproved = $isApproved;

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

    public function getSupportFile(): ?string
    {
        return $this->supportFile;
    }

    public function setSupportFile(?string $supportFile): static
    {
        $this->supportFile = $supportFile;

        return $this;
    }

    public function isArchived(): bool
    {
        return $this->isArchived;
    }

    public function setArchived(bool $isArchived): static
    {
        $this->isArchived = $isArchived;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(?string $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function __construct()
  {
    $this->createdAt = new \DateTimeImmutable();
    $this->isApproved = false;
    $this->quizzes = new ArrayCollection();
    $this->ressources = new ArrayCollection();
    $this->users = new ArrayCollection();
  }

    /**
     * @return Collection<int, Quizfor>
     */
    public function getQuizzes(): Collection
    {
        return $this->quizzes;
    }

    public function addQuiz(Quizfor $quiz): static
    {
        if (!$this->quizzes->contains($quiz)) {
            $this->quizzes->add($quiz);
            $quiz->setFormation($this);
        }

        return $this;
    }

    public function removeQuiz(Quizfor $quiz): static
    {
        if ($this->quizzes->removeElement($quiz)) {
            // set the owning side to null (unless already changed)
            if ($quiz->getFormation() === $this) {
                $quiz->setFormation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addFormation($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeFormation($this);
        }

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
     * @return Collection<int, Ressources>
     */
    public function getRessources(): Collection
    {
        return $this->ressources;
    }

    public function addRessource(Ressources $ressource): static
    {
        if (!$this->ressources->contains($ressource)) {
            $this->ressources->add($ressource);
            $ressource->setFormation($this);
        }

        return $this;
    }

    public function removeRessource(Ressources $ressource): static
    {
        if ($this->ressources->removeElement($ressource)) {
            // set the owning side to null (unless already changed)
            if ($ressource->getFormation() === $this) {
                $ressource->setFormation(null);
            }
        }

        return $this;
    }
}
