<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @return Event[]
     */
    public function findApproved(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.status = :status')
            ->setParameter('status', Event::STATUS_APPROVED)
            ->orderBy('e.eventDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Event[]
     */
    public function findPending(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.status = :status')
            ->setParameter('status', Event::STATUS_PENDING)
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countPending(): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.status = :status')
            ->setParameter('status', Event::STATUS_PENDING)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countApproved(): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.status = :status')
            ->setParameter('status', Event::STATUS_APPROVED)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
