<?php

namespace App\Repository;

use App\Entity\Cours;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cours>
 */
class CoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cours::class);
    }

    /**
     * Find all approved courses
     *
     * @return Cours[]
     */
    public function findApproved(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', Cours::STATUS_APPROVED)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all pending courses
     *
     * @return Cours[]
     */
    public function findPending(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', Cours::STATUS_PENDING)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all refused courses
     *
     * @return Cours[]
     */
    public function findRefused(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', Cours::STATUS_REFUSED)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find courses by creator (instructor)
     *
     * @return Cours[]
     */
    public function findByCreator(User $creator): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.creator = :creator')
            ->setParameter('creator', $creator)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find approved courses excluding those created by the user
     *
     * @return Cours[]
     */
    public function findApprovedExcludingCreator(User $creator): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.status = :status')
            ->andWhere('c.creator != :creator')
            ->setParameter('status', Cours::STATUS_APPROVED)
            ->setParameter('creator', $creator)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get count of pending courses
     */
    public function countPending(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.status = :status')
            ->setParameter('status', Cours::STATUS_PENDING)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get count of approved courses
     */
    public function countApproved(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.status = :status')
            ->setParameter('status', Cours::STATUS_APPROVED)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get count of refused courses
     */
    public function countRefused(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.status = :status')
            ->setParameter('status', Cours::STATUS_REFUSED)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
