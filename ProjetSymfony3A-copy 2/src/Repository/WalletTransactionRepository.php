<?php

namespace App\Repository;

use App\Entity\WalletTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WalletTransaction>
 */
class WalletTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WalletTransaction::class);
    }

    public function save(WalletTransaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(WalletTransaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find a wallet transaction by Stripe session ID
     */
    public function findByStripeSessionId(string $sessionId): ?WalletTransaction
    {
        return $this->findOneBy(['stripeSessionId' => $sessionId]);
    }

    /**
     * Find pending transactions for a user
     */
    /**
     * @return WalletTransaction[]
     */
    public function findPendingByUser(int $userId): array
    {
        return $this->findBy([
            'user' => $userId,
            'status' => 'pending'
        ], ['createdAt' => 'DESC']);
    }

    /**
     * Find paid transactions for a user
     */
    /**
     * @return WalletTransaction[]
     */
    public function findPaidByUser(int $userId): array
    {
        return $this->findBy([
            'user' => $userId,
            'status' => 'paid'
        ], ['completedAt' => 'DESC']);
    }
}
