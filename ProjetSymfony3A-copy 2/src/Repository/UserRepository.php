<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function countByRole(string $role): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"' . $role . '"%')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPending(string $role): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.roles LIKE :role')
            ->andWhere('u.isApproved = :approved')
            ->andWhere('u.isRejected = :rejected')
            ->setParameter('role', '%"' . $role . '"%')
            ->setParameter('approved', false)
            ->setParameter('rejected', false)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countApproved(string $role): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.roles LIKE :role')
            ->andWhere('u.isApproved = :approved')
            ->setParameter('role', '%"' . $role . '"%')
            ->setParameter('approved', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countRejected(string $role): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.roles LIKE :role')
            ->andWhere('u.isRejected = :rejected')
            ->setParameter('role', '%"' . $role . '"%')
            ->setParameter('rejected', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countBlocked(): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.isBlocked = :blocked')
            ->setParameter('blocked', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findUsersByRoleAndStatus(string $role, ?string $status = null, ?string $search = null, string $sort = 'id', string $direction = 'DESC'): \Doctrine\ORM\QueryBuilder
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"' . $role . '"%');

        if ($status) {
            switch ($status) {
                case 'pending':
                    $qb->andWhere('u.isApproved = :approved AND u.isRejected = :rejected')
                       ->setParameter('approved', false)
                       ->setParameter('rejected', false);
                    break;
                case 'approved':
                    $qb->andWhere('u.isApproved = :approved')
                       ->setParameter('approved', true);
                    break;
                case 'rejected':
                    $qb->andWhere('u.isRejected = :rejected')
                       ->setParameter('rejected', true);
                    break;
                case 'blocked':
                    $qb->andWhere('u.isBlocked = :blocked')
                       ->setParameter('blocked', true);
                    break;
            }
        }

        if ($search) {
            $qb->andWhere('u.fullName LIKE :search OR u.email LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $qb->orderBy('u.' . $sort, $direction);

        return $qb;
    }
}
