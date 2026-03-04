<?php

namespace App\Repository;

use App\Entity\Ressources;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ressources>
 *
 * @method Ressources|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ressources|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ressources[]    findAll()
 * @method Ressources[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RessourcesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ressources::class);
    }

    public function save(Ressources $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Ressources $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
