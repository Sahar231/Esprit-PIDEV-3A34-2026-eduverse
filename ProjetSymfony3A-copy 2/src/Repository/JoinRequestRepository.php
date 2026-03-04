<?php

namespace App\Repository;

use App\Entity\JoinRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JoinRequest>
 */
class JoinRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JoinRequest::class);
    }

    /**
     * @return JoinRequest[]
     */
    public function findPending(): array
    {
        return $this->createQueryBuilder('jr')
            ->where('jr.status = :status')
            ->setParameter('status', JoinRequest::STATUS_PENDING)
            ->orderBy('jr.requestedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countPending(): int
    {
        return (int) $this->createQueryBuilder('jr')
            ->select('COUNT(jr.id)')
            ->where('jr.status = :status')
            ->setParameter('status', JoinRequest::STATUS_PENDING)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param \App\Entity\Club|int $club
     * @param string|int $status
     * @return JoinRequest[]
     */
    public function findByClubAndStatus(\App\Entity\Club|int $club, string|int $status): array
    {
        return $this->createQueryBuilder('jr')
            ->where('jr.club = :club')
            ->andWhere('jr.status = :status')
            ->setParameter('club', $club)
            ->setParameter('status', $status)
            ->orderBy('jr.requestedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
