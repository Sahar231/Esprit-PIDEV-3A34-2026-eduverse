<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $question = null;

    #[ORM\Column(length: 255)]
    private ?string $reply = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $score = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $choices = null;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quizfor $quiz = null;

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

    public function getReply(): ?string
    {
        return $this->reply;
    }

    public function setReply(string $reply): static
    {
        $this->reply = $reply;

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

    public function getQuiz(): ?Quizfor
    {
        return $this->quiz;
    }

    public function setQuiz(?Quizfor $quiz): static
    {
        $this->quiz = $quiz;

        return $this;
    }
}
