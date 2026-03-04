<?php

namespace App\Entity;

use App\Repository\QuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizRepository::class)]
#[ORM\Table(name: 'quiz')]
class Quizfor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Title = null;

    #[ORM\Column(length: 255)]
    private ?string $Description = null;

    #[ORM\Column(length: 255)]
    private ?string $Category = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $total_score = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $Pass_Score = null;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['default' => 0])]
    private ?int $Total_Questions = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $CreatedOn = null;

    #[ORM\Column(length: 255)]
    private ?string $Difficulty = null;

    #[ORM\ManyToOne(inversedBy: 'quizzes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Formation $Formation = null;

    /**
     * @var Collection<int, Question>
     */
    #[ORM\OneToMany(targetEntity: Question::class, mappedBy: 'quiz', orphanRemoval: true)]
    private Collection $questions;



  public function __construct(?Formation $formation = null)
    {
        $this->Formation = $formation;
        $this->CreatedOn = new \DateTimeImmutable();
        $this->questions = new ArrayCollection();
        // ensure the total questions is never null
        $this->Total_Questions = 0;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->Title;
    }

    public function setTitle(string $Title): static
    {
        $this->Title = $Title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): static
    {
        $this->Description = $Description;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->Category;
    }

    public function setCategory(string $Category): static
    {
        $this->Category = $Category;

        return $this;
    }

    public function getTotalScore(): ?string
    {
        return $this->total_score;
    }

    public function setTotalScore(string $total_score): static
    {
        $this->total_score = $total_score;

        return $this;
    }

    public function getPassScore(): ?string
    {
        return $this->Pass_Score;
    }

    public function setPassScore(string $Pass_Score): static
    {
        $this->Pass_Score = $Pass_Score;

        return $this;
    }

    public function getTotalQuestions(): ?int
    {
        return $this->Total_Questions;
    }

    public function setTotalQuestions(int $Total_Questions): static
    {
        $this->Total_Questions = $Total_Questions;

        return $this;
    }

    public function getCreatedOn(): ?\DateTimeImmutable
    {
        return $this->CreatedOn;
    }

    public function setCreatedOn(\DateTimeImmutable $CreatedOn): static
    {
        $this->CreatedOn = $CreatedOn;

        return $this;
    }

    public function getDifficulty(): ?string
    {
        return $this->Difficulty;
    }

    public function setDifficulty(string $Difficulty): static
    {
        $this->Difficulty = $Difficulty;

        return $this;
    }

    public function getFormation(): ?Formation
    {
        return $this->Formation;
    }

    public function setFormation(?Formation $Formation): static
    {
        $this->Formation = $Formation;

        return $this;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): static
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setQuiz($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): static
    {
        if ($this->questions->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getQuiz() === $this) {
                $question->setQuiz(null);
            }
        }

        return $this;
    }


}
