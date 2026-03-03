<?php

namespace App\Command;

use App\Entity\Quiz;
use App\Entity\Question;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:reset-and-load',
    description: 'Réinitialise complètement la base et charge les données',
)]
class ResetAndLoadCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            // Clear existing data
            $io->info('🗑️  Suppression des données existantes...');
            $this->em->createQuery('DELETE FROM App\Entity\Reponse')->execute();
            $this->em->createQuery('DELETE FROM App\Entity\Question')->execute();
            $this->em->createQuery('DELETE FROM App\Entity\Quiz')->execute();
            $io->success('✅ Données supprimées');

            // Load quiz 1: PHP Basics
            $io->info('📝 Chargement du quiz: PHP Basics');
            $quiz1 = new Quiz();
            $quiz1->setTitle('PHP Basics');
            $quiz1->setLevel('facile');
            $quiz1->setDuration(15);

            $questions1 = [
                ['Quel est le symbole pour afficher du texte en PHP?', 'echo'],
                ['Comment déclarer une variable en PHP?', '$'],
                ['Quel est le symbole pour commenter une ligne en PHP?', '//'],
                ['Quelle fonction retourne la longueur d\'une chaîne?', 'strlen'],
                ['Quel type de données pour un tableau en PHP?', 'array'],
            ];

            foreach ($questions1 as [$text, $answer]) {
                $q = new Question();
                $q->setText($text);
                $q->setCorrectAnswer($answer);
                $q->setQuiz($quiz1);
                $this->em->persist($q);
            }
            $this->em->persist($quiz1);

            // Load quiz 2: JavaScript ES6
            $io->info('📝 Chargement du quiz: JavaScript ES6');
            $quiz2 = new Quiz();
            $quiz2->setTitle('JavaScript ES6');
            $quiz2->setLevel('intermediaire');
            $quiz2->setDuration(20);

            $questions2 = [
                ['Quel mot-clé déclare une variable constante?', 'const'],
                ['Comment créer une fonction fléchée en JavaScript?', '=>'],
                ['Quel est le type de données pour un objet en JS?', 'object'],
                ['Comment sélectionner un élément HTML par ID?', 'getElementById'],
                ['Quel est l\'outil pour transpiler ES6 en ES5?', 'Babel'],
            ];

            foreach ($questions2 as [$text, $answer]) {
                $q = new Question();
                $q->setText($text);
                $q->setCorrectAnswer($answer);
                $q->setQuiz($quiz2);
                $this->em->persist($q);
            }
            $this->em->persist($quiz2);

            // Load quiz 3: SQL Database
            $io->info('📝 Chargement du quiz: SQL Database');
            $quiz3 = new Quiz();
            $quiz3->setTitle('SQL Database');
            $quiz3->setLevel('difficile');
            $quiz3->setDuration(30);

            $questions3 = [
                ['Quel mot-clé sélectionne toutes les colonnes?', '*'],
                ['Quel est le type de jointure qui retourne les lignes des deux tables?', 'INNER JOIN'],
                ['Comment insérer des données dans une table?', 'INSERT'],
                ['Quel mot-clé crée une nouvelle table?', 'CREATE TABLE'],
                ['Quel agrégat compte le nombre de lignes?', 'COUNT'],
            ];

            foreach ($questions3 as [$text, $answer]) {
                $q = new Question();
                $q->setText($text);
                $q->setCorrectAnswer($answer);
                $q->setQuiz($quiz3);
                $this->em->persist($q);
            }
            $this->em->persist($quiz3);

            // Load quiz 4: Symfony Framework
            $io->info('📝 Chargement du quiz: Symfony Framework');
            $quiz4 = new Quiz();
            $quiz4->setTitle('Symfony Framework');
            $quiz4->setLevel('intermediaire');
            $quiz4->setDuration(25);

            $questions4 = [
                ['Quel est le gestionnaire d\'entités Doctrine?', 'EntityManager'],
                ['Quel dossier contient les templates Twig?', 'templates'],
                ['Quel est le fichier config principal de Symfony?', 'services.yaml'],
                ['Comment générer une entité Doctrine?', 'make:entity'],
                ['Quel moteur de templates utilise Symfony?', 'Twig'],
            ];

            foreach ($questions4 as [$text, $answer]) {
                $q = new Question();
                $q->setText($text);
                $q->setCorrectAnswer($answer);
                $q->setQuiz($quiz4);
                $this->em->persist($q);
            }
            $this->em->persist($quiz4);

            // Save everything
            $this->em->flush();

            $io->success('✅ 4 quizzes avec 20 questions chargés avec succès!');

            // Verify
            $count = count($this->em->getRepository(Quiz::class)->findAll());
            $io->info("📊 Vérification: $count quizzes dans la base de données");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('❌ Erreur: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
