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
    name: 'app:load-quiz-data',
    description: 'Charge les données de quiz de test dans la base de données',
)]
class LoadQuizDataCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Chargement des données de quiz...');

        // Quiz 1: PHP Basics
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
            $this->entityManager->persist($q);
        }
        $this->entityManager->persist($quiz1);

        // Quiz 2: JavaScript ES6
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
            $this->entityManager->persist($q);
        }
        $this->entityManager->persist($quiz2);

        // Quiz 3: SQL Database
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
            $this->entityManager->persist($q);
        }
        $this->entityManager->persist($quiz3);

        // Quiz 4: Symfony Framework
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
            $this->entityManager->persist($q);
        }
        $this->entityManager->persist($quiz4);

        // Flush all data
        $this->entityManager->flush();

        $io->success('4 quiz avec 20 questions ont été chargés avec succès!');
        $io->info('Quiz chargés: PHP Basics, JavaScript ES6, SQL Database, Symfony Framework');

        return Command::SUCCESS;
    }
}
