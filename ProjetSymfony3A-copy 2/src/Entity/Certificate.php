<?php

namespace App\Entity;

use App\Repository\CertificateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CertificateRepository::class)]
class Certificate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'certificates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Formation::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Formation $formation = null;

    #[ORM\ManyToOne(targetEntity: Quizfor::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Quizfor $quiz = null;

    #[ORM\Column]
    private ?float $score = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $awardedAt = null;

    public function __construct()
    {
        $this->awardedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getFormation(): ?Formation
    {
        return $this->formation;
    }

    public function setFormation(?Formation $formation): static
    {
        $this->formation = $formation;

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

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function setScore(float $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getAwardedAt(): ?\DateTimeImmutable
    {
        return $this->awardedAt;
    }

    public function setAwardedAt(\DateTimeImmutable $awardedAt): static
    {
        $this->awardedAt = $awardedAt;

        return $this;
    }
}
