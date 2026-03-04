<?php

namespace App\Command;

use App\Entity\Quiz;
use App\Entity\Resultat;
use App\Entity\User;
use App\Entity\WalletTransaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-analytics-data',
    description: 'Seed test data for analytics dashboard'
)]
class SeedAnalyticsDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Starting analytics data seeding...');

        // Get existing users
        $users = $this->entityManager->getRepository(User::class)->findAll();
        if (empty($users)) {
            $io->warning('No users found in database');
            return Command::FAILURE;
        }

        $io->info('Found ' . count($users) . ' users');

        // Create Resultat records (enrollment data)
        $resultCount = $this->createResultats($users);
        $io->success('Created ' . $resultCount . ' resultat records (enrollment data)');

        // Create test wallet transactions
        $transactionCount = $this->createWalletTransactions($users);
        $io->success('Created ' . $transactionCount . ' wallet transactions');

        $io->success('Analytics data seeding completed successfully!');
        $io->note('The analytics dashboard will now show data based on user activities');
        return Command::SUCCESS;
    }

    /**
     * @param User[] $users
     */
    private function createResultats(array $users): int
    {
        $resultCount = 0;

        // Get or create a test quiz
        $quiz = $this->entityManager->getRepository(Quiz::class)->findOneBy([]);
        if (!$quiz) {
            return 0; // No quizzes in database yet
        }

        // Create resultat records (quiz completions that count asresult enrollments)
        foreach ($users as $user) {
            // Check if user already has results
            $existing = $this->entityManager->getRepository(Resultat::class)
                ->findBy(['student' => $user]);
            
            if (!empty($existing)) {
                continue; // Skip if user already has data
            }

            // Create 1-3 result records per user
            for ($i = 0; $i < rand(1, 3); $i++) {
                $resultat = new Resultat();
                $resultat->setStudent($user);
                $resultat->setQuiz($quiz);
                // score stored as string in entity
                $resultat->setScore((string) rand(40, 100));
                $resultat->setCreatedAt(new \DateTimeImmutable('-' . rand(1, 20) . ' days'));

                $this->entityManager->persist($resultat);
                $resultCount++;
            }
        }

        $this->entityManager->flush();
        return $resultCount;
    }

    /**
     * @param User[] $users
     */
    private function createWalletTransactions(array $users): int
    {
        $transactionCount = 0;

        foreach ($users as $user) {
            // Check if transactions already exist
            $existing = $this->entityManager->getRepository(WalletTransaction::class)
                ->findBy(['user' => $user]);
            
            if (!empty($existing)) {
                continue;
            }

            // Create 2-4 transactions per user
            for ($i = 0; $i < rand(2, 4); $i++) {
                $daysAgo = rand(1, 25);
                $transaction = new WalletTransaction();
                $transaction->setUser($user);
                $transaction->setCredits(rand(20, 100));
                // amount stored as string in entity
                $transaction->setAmount((string) rand(10, 50));
                $transaction->setStatus('paid');
                $transaction->setCompletedAt(new \DateTimeImmutable('-' . $daysAgo . ' days'));
                $transaction->setCreatedAt(new \DateTimeImmutable('-' . $daysAgo . ' days'));

                $this->entityManager->persist($transaction);
                $transactionCount++;
            }
        }

        $this->entityManager->flush();
        return $transactionCount;
    }
}



