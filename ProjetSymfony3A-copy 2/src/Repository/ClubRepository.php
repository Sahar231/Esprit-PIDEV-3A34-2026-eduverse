<?php

namespace App\Repository;

use App\Entity\Club;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Club>
 */
class ClubRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Club::class);
    }

    /**
     * @return Club[]
     */
    public function findApproved(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', Club::STATUS_APPROVED)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Club[]
     */
    public function findPending(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', Club::STATUS_PENDING)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string|int $status
     * @return Club[]
     */
    public function findByStatus(string|int $status): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', $status)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countApproved(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.status = :status')
            ->setParameter('status', Club::STATUS_APPROVED)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPending(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.status = :status')
            ->setParameter('status', Club::STATUS_PENDING)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countRejected(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.status = :status')
            ->setParameter('status', Club::STATUS_REJECTED)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
