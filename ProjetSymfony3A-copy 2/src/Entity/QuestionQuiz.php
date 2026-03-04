<?php

namespace App\Entity;

use App\Repository\QuestionQuizRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuestionQuizRepository::class)]
#[ORM\Table(name: 'question_quiz')]
class QuestionQuiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Question text is required')]
    #[Assert\Length(
        min: 5,
        max: 2000,
        minMessage: 'Question must be at least 5 characters',
        maxMessage: 'Question cannot exceed 2000 characters'
    )]
    #[Assert\Regex(
        pattern: '/^[A-Z]/',
        message: 'Question must start with an uppercase letter (A-Z)'
    )]
    private ?string $question = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Correct answer is required')]
    #[Assert\Length(
        min: 1,
        max: 500,
        minMessage: 'Please provide a correct answer',
        maxMessage: 'Answer cannot exceed 500 characters'
    )]
    #[Assert\Regex(
        pattern: '/^[A-Z]/',
        message: 'Answer must start with an uppercase letter (A-Z)'
    )]
    private ?string $correctAnswer = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Score is required')]
    #[Assert\PositiveOrZero(message: 'Score must be 0 or positive')]
    private ?string $score = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Please select a question type')]
    private ?string $type = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Assert\Count(
        min: 2,
        minMessage: 'You must provide at least 2 answer choices'
    )]
    private ?array $choices = null;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $quiz = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getCorrectAnswer(): ?string
    {
        return $this->correctAnswer;
    }

    public function setCorrectAnswer(string $correctAnswer): static
    {
        $this->correctAnswer = $correctAnswer;

        return $this;
    }

    public function getScore(): ?string
    {
        return $this->score;
    }

    public function setScore(string $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getChoices(): ?array
    {
        return $this->choices;
    }

    public function setChoices(?array $choices): static
    {
        $this->choices = $choices;

        return $this;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): static
    {
        $this->quiz = $quiz;

        return $this;
    }
}
