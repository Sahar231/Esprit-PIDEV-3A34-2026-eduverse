<?php

namespace App\Service;

use App\Entity\Certificate;
use App\Entity\Formation;
use App\Entity\Quizfor;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CertificateService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * Award a certificate to a student for completing a quiz/formation
     */
    public function awardCertificate(
        User $user,
        Formation $formation,
        float $score,
        ?Quizfor $quiz = null
    ): Certificate {
        $certificate = new Certificate();
        $certificate->setUser($user);
        $certificate->setFormation($formation);
        $certificate->setScore($score);
        if ($quiz) {
            $certificate->setQuiz($quiz);
        }

        $this->entityManager->persist($certificate);
        $this->entityManager->flush();

        return $certificate;
    }

    /**
     * Check if a student already has a certificate for a formation
     */
    public function hasCertificate(User $user, Formation $formation): bool
    {
        /** @var \App\Repository\CertificateRepository $repo */
        $repo = $this->entityManager->getRepository(Certificate::class);
        $certificate = $repo->findByUserAndFormation($user, $formation);

        return $certificate !== null;
    }

    /**
     * Get a certificate for a user and formation
     */
    public function getCertificate(User $user, Formation $formation): ?Certificate
    {
        /** @var \App\Repository\CertificateRepository $repo */
        $repo = $this->entityManager->getRepository(Certificate::class);
        return $repo->findByUserAndFormation($user, $formation);
    }

    /**
     * Award certificate if quiz is passed (score >= pass score)
     */
    public function awardCertificateIfPassed(
        User $user,
        Formation $formation,
        Quizfor $quiz,
        float $score
    ): ?Certificate {
        // Check if score is passing
        $passScore = (float) $quiz->getPassScore();
        
        if ($score >= $passScore) {
            // Check if certificate already exists
            if (!$this->hasCertificate($user, $formation)) {
                return $this->awardCertificate($user, $formation, $score, $quiz);
            }
        }

        return null;
    }
}
