<?php

namespace App\Repository;

use App\Entity\Chapitre;
use App\Entity\Cours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Chapitre>
 */
class ChapitreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chapitre::class);
    }

    /**
     * Find all chapters for a specific course
     */
    public function findByCours(Cours $cours): array
    {
        return $this->createQueryBuilder('ch')
            ->where('ch.cours = :cours')
            ->setParameter('cours', $cours)
            ->orderBy('ch.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get count of chapters for a course
     */
    public function countByCours(Cours $cours): int
    {
        return (int) $this->createQueryBuilder('ch')
            ->select('COUNT(ch.id)')
            ->where('ch.cours = :cours')
            ->setParameter('cours', $cours)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find chapter by id and course
     */
    public function findOneByIdAndCours(int $id, Cours $cours): ?Chapitre
    {
        return $this->createQueryBuilder('ch')
            ->where('ch.id = :id')
            ->andWhere('ch.cours = :cours')
            ->setParameter('id', $id)
            ->setParameter('cours', $cours)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
