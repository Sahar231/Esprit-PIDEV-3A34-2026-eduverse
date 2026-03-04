<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'L\'adresse e-mail est obligatoire.')]
    #[Assert\Email(message: 'Adresse e-mail invalide.')]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleId = null;

    /**
     * @var string|null The hashed password
     */
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(min: 2, max: 50, minMessage: 'Minimum 2 caractères.', maxMessage: 'Maximum 50 caractères.')]
    private ?string $fullName = null;

    #[ORM\Column(length: 255)]
    private ?string $role = null;

    #[ORM\Column]
    private ?bool $isApproved = false;

    #[ORM\Column(options: ["default" => false])]
    private ?bool $isTwoFactorEnabled = false;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 6, nullable: true)]
    private ?string $twoFactorCode = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $twoFactorExpiresAt = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $faceDescriptor = null;

    #[ORM\Column]
    private ?bool $isBlocked = false;

    #[ORM\Column]
    private ?bool $isRejected = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picture = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $jobTitle = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bio = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Wallet $wallet = null;

    /**
     * @var Collection<int, WalletTransaction>
     */
    #[ORM\OneToMany(targetEntity: WalletTransaction::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $walletTransactions;

    /**
     * @var Collection<int, Formation>
     */
    #[ORM\ManyToMany(targetEntity: Formation::class, inversedBy: 'users')]
    private Collection $formations;

    /**
     * @var Collection<int, Certificate>
     */
    #[ORM\OneToMany(targetEntity: Certificate::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $certificates;

    /**
     * @var Collection<int, Resultat>
     */
    #[ORM\OneToMany(targetEntity: Resultat::class, mappedBy: 'student')]
    private Collection $resultats;

    public function __construct()
    {
        $this->formations = new ArrayCollection();
        $this->walletTransactions = new ArrayCollection();
        $this->certificates = new ArrayCollection();
        $this->resultats = new ArrayCollection();
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): static
    {
        $this->googleId = $googleId;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

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

    public function isBlocked(): ?bool
    {
        return $this->isBlocked;
    }

    public function setIsBlocked(bool $isBlocked): static
    {
        $this->isBlocked = $isBlocked;

        return $this;
    }

    public function isRejected(): ?bool
    {
        return $this->isRejected;
    }

    public function setIsRejected(bool $isRejected): static
    {
        $this->isRejected = $isRejected;

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

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function setJobTitle(?string $jobTitle): static
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }



    /**
     * @return Collection<int, Formation>
     */
    public function getFormations(): Collection
    {
        return $this->formations;
    }

    public function addFormation(Formation $formation): static
    {
        if (!$this->formations->contains($formation)) {
            $this->formations->add($formation);
        }

        return $this;
    }

    public function removeFormation(Formation $formation): static
    {
        $this->formations->removeElement($formation);

        return $this;
    }

    public function getWallet(): ?Wallet
    {
        return $this->wallet;
    }

    public function setWallet(?Wallet $wallet): static
    {
        $this->wallet = $wallet;

        return $this;
    }

    /**
     * @return Collection<int, WalletTransaction>
     */
    public function getWalletTransactions(): Collection
    {
        return $this->walletTransactions;
    }

    public function addWalletTransaction(WalletTransaction $transaction): static
    {
        if (!$this->walletTransactions->contains($transaction)) {
            $this->walletTransactions->add($transaction);
            $transaction->setUser($this);
        }

        return $this;
    }

    public function removeWalletTransaction(WalletTransaction $transaction): static
    {
        if ($this->walletTransactions->removeElement($transaction)) {
            if ($transaction->getUser() === $this) {
                $transaction->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Certificate>
     */
    public function getCertificates(): Collection
    {
        return $this->certificates;
    }

    public function addCertificate(Certificate $certificate): static
    {
        if (!$this->certificates->contains($certificate)) {
            $this->certificates->add($certificate);
            $certificate->setUser($this);
        }

        return $this;
    }

    public function removeCertificate(Certificate $certificate): static
    {
        if ($this->certificates->removeElement($certificate)) {
            if ($certificate->getUser() === $this) {
                $certificate->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection<int, \App\Entity\Resultat>
     */
    public function getResultats(): Collection
    {
        return $this->resultats;
    }

    public function addResultat(Resultat $resultat): static
    {
        if (!$this->resultats->contains($resultat)) {
            $this->resultats->add($resultat);
            $resultat->setStudent($this);
        }

        return $this;
    }

    public function removeResultat(Resultat $resultat): static
    {
        if ($this->resultats->removeElement($resultat)) {
            if ($resultat->getStudent() === $this) {
                $resultat->setStudent(null);
            }
        }

        return $this;
    }

    public function isTwoFactorEnabled(): bool
    {
        return $this->isTwoFactorEnabled;
    }

    public function setIsTwoFactorEnabled(bool $isTwoFactorEnabled): static
    {
        $this->isTwoFactorEnabled = $isTwoFactorEnabled;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getTwoFactorCode(): ?string
    {
        return $this->twoFactorCode;
    }

    public function setTwoFactorCode(?string $twoFactorCode): static
    {
        $this->twoFactorCode = $twoFactorCode;

        return $this;
    }

    public function getTwoFactorExpiresAt(): ?\DateTimeImmutable
    {
        return $this->twoFactorExpiresAt;
    }

    public function setTwoFactorExpiresAt(?\DateTimeImmutable $twoFactorExpiresAt): static
    {
        $this->twoFactorExpiresAt = $twoFactorExpiresAt;

        return $this;
    }

    public function getFaceDescriptor(): ?array
    {
        return $this->faceDescriptor;
    }

    public function setFaceDescriptor(?array $faceDescriptor): static
    {
        $this->faceDescriptor = $faceDescriptor;

        return $this;
    }
}
