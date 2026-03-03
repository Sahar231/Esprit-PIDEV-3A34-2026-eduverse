<?php

namespace App\Command;

use App\Entity\Quiz;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-db',
    description: 'Teste la connexion et compte les quiz',
)]
class TestDatabaseCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            // Test connection
            $conn = $this->em->getConnection();
            $conn->executeQuery('SELECT 1');
            $io->success('✅ Base de données connectée!');
        } catch (\Exception $e) {
            $io->error('❌ Erreur de connexion: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // Count quizzes
        $quizRepository = $this->em->getRepository(Quiz::class);
        $count = count($quizRepository->findAll());

        $io->info("Total de Quiz: <fg=green>$count</>");

        if ($count > 0) {
            $io->success('✅ Les données sont présentes!');
            foreach ($quizRepository->findAll() as $quiz) {
                $io->text("  - " . $quiz->getTitle() . " (" . count($quiz->getQuestions()) . " questions)");
            }
        } else {
            $io->error('❌ Aucun quiz trouvé. Exécutez: php bin/console app:load-quiz-data');
        }

        return Command::SUCCESS;
    }
}
