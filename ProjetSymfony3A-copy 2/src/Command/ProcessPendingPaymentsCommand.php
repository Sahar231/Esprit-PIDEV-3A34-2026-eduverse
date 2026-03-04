<?php

namespace App\Command;

use App\Repository\WalletTransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:process-pending-payments',
    description: 'Process pending wallet transactions (useful for local testing without Stripe webhooks)',
)]
class ProcessPendingPaymentsCommand extends Command
{
    public function __construct(
        private WalletTransactionRepository $transactionRepository,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Find all pending transactions
        $pendingTransactions = $this->transactionRepository->findBy(['status' => 'pending']);

        if (empty($pendingTransactions)) {
            $io->info('No pending transactions to process.');
            return Command::SUCCESS;
        }

        $io->info('Processing ' . count($pendingTransactions) . ' pending transaction(s)...');

        foreach ($pendingTransactions as $transaction) {
            $user = $transaction->getUser();
            $wallet = $user->getWallet();

            if (!$wallet) {
                $io->error('User ' . $user->getId() . ' has no wallet. Skipping transaction ' . $transaction->getId());
                continue;
            }

            // Add credits to wallet
            $wallet->addBalance($transaction->getCredits());

            // Mark transaction as paid
            $transaction->setStatus('paid');
            $transaction->setCompletedAt(new \DateTimeImmutable());

            $io->success(
                'Transaction #' . $transaction->getId() . 
                ' processed: Added ' . $transaction->getCredits() . 
                ' credits to user ' . $user->getEmail() . 
                ' (new balance: ' . $wallet->getBalance() . ')'
            );
        }

        // Save all changes
        $this->entityManager->flush();

        $io->success('All pending payments processed successfully!');
        return Command::SUCCESS;
    }
}
