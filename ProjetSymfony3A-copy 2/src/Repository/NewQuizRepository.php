<?php

namespace App\Repository;

use App\Entity\Quiz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Quiz>
 */
class NewQuizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quiz::class);
    }

    /**
     * Find all approved quizzes that are not archived
     *
     * @return Quiz[]
     */
    public function findApproved(): array
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.state = :state')
            ->andWhere('q.isApproved = :approved')
            ->andWhere('q.isArchived = :archived')
            ->setParameter('state', 'approved')
            ->setParameter('approved', true)
            ->setParameter('archived', false)
            ->orderBy('q.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Find all pending quizzes (awaiting admin approval)
     *
     * @return Quiz[]
     */
    public function findPending(): array
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.state = :state')
            ->setParameter('state', 'pending')
            ->orderBy('q.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
