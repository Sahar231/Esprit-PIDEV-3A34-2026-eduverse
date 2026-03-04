<?php

namespace App\Entity;

use App\Repository\NewQuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NewQuizRepository::class)]
#[ORM\Table(name: 'quiz_assessment')]
class Quiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Quiz title is required')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Quiz title must be at least 3 characters',
        maxMessage: 'Quiz title cannot exceed 255 characters'
    )]
    #[Assert\Regex(
        pattern: '/^[A-Z]/',
        message: 'Quiz title must start with an uppercase letter (A-Z)'
    )]
    private ?string $title = null;

    #[ORM\Column]
    private ?bool $isApproved = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $state = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\NotBlank(message: 'Please select a level')]
    private ?string $level = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: 'Duration must be a positive number')]
    private ?int $duration = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isArchived = false;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $creator = null;

    /**
     * @var Collection<int, QuestionQuiz>
     */
    #[ORM\OneToMany(targetEntity: QuestionQuiz::class, mappedBy: 'quiz', orphanRemoval: true, cascade: ['persist', 'remove'])]
    #[Assert\Count(
        min: 1,
        minMessage: 'You must add at least 1 question'
    )]
    private Collection $questions;

    /**
     * @var Collection<int, Resultat>
     */
    #[ORM\OneToMany(targetEntity: Resultat::class, mappedBy: 'quiz', orphanRemoval: true)]
    private Collection $resultats;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->isApproved = false;
        $this->state = 'pending';
        $this->isArchived = false;
        $this->questions = new ArrayCollection();
        $this->resultats = new ArrayCollection();
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

    public function isApproved(): ?bool
    {
        return $this->isApproved;
    }

    public function setIsApproved(bool $isApproved): static
    {
        $this->isApproved = $isApproved;
        if ($isApproved) {
            $this->state = 'approved';
        }

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): static
    {
        $this->state = $state;

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

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;

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

    public function isArchived(): bool
    {
        return $this->isArchived;
    }

    public function setArchived(bool $isArchived): static
    {
        $this->isArchived = $isArchived;

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
     * @return Collection<int, QuestionQuiz>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(QuestionQuiz $question): static
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setQuiz($this);
        }

        return $this;
    }

    public function removeQuestion(QuestionQuiz $question): static
    {
        if ($this->questions->removeElement($question)) {
            if ($question->getQuiz() === $this) {
                $question->setQuiz(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Resultat>
     */
    public function getResultats(): Collection
    {
        return $this->resultats;
    }

    public function addResultat(Resultat $resultat): static
    {
        if (!$this->resultats->contains($resultat)) {
            $this->resultats->add($resultat);
            $resultat->setQuiz($this);
        }

        return $this;
    }

    public function removeResultat(Resultat $resultat): static
    {
        if ($this->resultats->removeElement($resultat)) {
            if ($resultat->getQuiz() === $this) {
                $resultat->setQuiz(null);
            }
        }

        return $this;
    }
}
