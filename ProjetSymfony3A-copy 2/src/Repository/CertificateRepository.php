<?php

namespace App\Repository;

use App\Entity\Certificate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Certificate>
 *
 * @method Certificate|null find($id, $lockMode = null, $lockVersion = null)
 * @method Certificate|null findOneBy(array $criteria, array $orderBy = null)
 * @method Certificate[]    findAll()
 * @method Certificate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CertificateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Certificate::class);
    }

    /**
     * @param \App\Entity\User|int $user
     * @return Certificate[]
     */
    public function findByUser(\App\Entity\User|int $user): array
    {
        return $this->findBy(['user' => $user], ['awardedAt' => 'DESC']);
    }

    /**
     * @param \App\Entity\User|int $user
     * @param \App\Entity\Formation|int $formation
     * @return Certificate|null
     */
    public function findByUserAndFormation(\App\Entity\User|int $user, \App\Entity\Formation|int $formation): ?Certificate
    {
        return $this->findOneBy(['user' => $user, 'formation' => $formation]);
    }

    /**
     * @param \App\Entity\User|int $user
     * @param \App\Entity\Quizfor|int $quizfor
     * @return Certificate|null
     */
    public function findByUserAndQuiz(\App\Entity\User|int $user, \App\Entity\Quizfor|int $quizfor): ?Certificate
    {
        return $this->findOneBy(['user' => $user, 'quiz' => $quizfor]);
    }
}
