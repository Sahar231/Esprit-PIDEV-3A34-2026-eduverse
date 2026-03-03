<?php

namespace App\DataFixtures;

use App\Entity\Quiz;
use App\Entity\Question;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class QuizFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Quiz 1: PHP Basics
        $quiz1 = new Quiz();
        $quiz1->setTitle('PHP Basics');
        $quiz1->setLevel('facile');
        $quiz1->setDuration(15);

        $question1 = new Question();
        $question1->setText('Quel est le symbole pour afficher du texte en PHP?');
        $question1->setCorrectAnswer('echo');
        $question1->setQuiz($quiz1);
        $manager->persist($question1);

        $question2 = new Question();
        $question2->setText('Comment déclarer une variable en PHP?');
        $question2->setCorrectAnswer('$');
        $question2->setQuiz($quiz1);
        $manager->persist($question2);

        $question3 = new Question();
        $question3->setText('Quel est le symbole pour commenter une ligne en PHP?');
        $question3->setCorrectAnswer('//');
        $question3->setQuiz($quiz1);
        $manager->persist($question3);

        $question4 = new Question();
        $question4->setText('Quelle fonction retourne la longueur d\'une chaîne?');
        $question4->setCorrectAnswer('strlen');
        $question4->setQuiz($quiz1);
        $manager->persist($question4);

        $question5 = new Question();
        $question5->setText('Quel type de données pour un tableau en PHP?');
        $question5->setCorrectAnswer('array');
        $question5->setQuiz($quiz1);
        $manager->persist($question5);

        $manager->persist($quiz1);

        // Quiz 2: JavaScript ES6
        $quiz2 = new Quiz();
        $quiz2->setTitle('JavaScript ES6');
        $quiz2->setLevel('intermediaire');
        $quiz2->setDuration(20);

        $question6 = new Question();
        $question6->setText('Quel mot-clé déclare une variable constante?');
        $question6->setCorrectAnswer('const');
        $question6->setQuiz($quiz2);
        $manager->persist($question6);

        $question7 = new Question();
        $question7->setText('Comment créer une fonction fléchée en JavaScript?');
        $question7->setCorrectAnswer('=>');
        $question7->setQuiz($quiz2);
        $manager->persist($question7);

        $question8 = new Question();
        $question8->setText('Quel est le type de données pour un objet en JS?');
        $question8->setCorrectAnswer('object');
        $question8->setQuiz($quiz2);
        $manager->persist($question8);

        $question9 = new Question();
        $question9->setText('Comment sélectionner un élément HTML par ID?');
        $question9->setCorrectAnswer('getElementById');
        $question9->setQuiz($quiz2);
        $manager->persist($question9);

        $question10 = new Question();
        $question10->setText('Quel est l\'outil pour transpiler ES6 en ES5?');
        $question10->setCorrectAnswer('Babel');
        $question10->setQuiz($quiz2);
        $manager->persist($question10);

        $manager->persist($quiz2);

        // Quiz 3: Database SQL
        $quiz3 = new Quiz();
        $quiz3->setTitle('SQL Database');
        $quiz3->setLevel('difficile');
        $quiz3->setDuration(30);

        $question11 = new Question();
        $question11->setText('Quel mot-clé sélectionne toutes les colonnes?');
        $question11->setCorrectAnswer('*');
        $question11->setQuiz($quiz3);
        $manager->persist($question11);

        $question12 = new Question();
        $question12->setText('Quel est le type de jointure qui retourne les lignes des deux tables?');
        $question12->setCorrectAnswer('INNER JOIN');
        $question12->setQuiz($quiz3);
        $manager->persist($question12);

        $question13 = new Question();
        $question13->setText('Comment insérer des données dans une table?');
        $question13->setCorrectAnswer('INSERT');
        $question13->setQuiz($quiz3);
        $manager->persist($question13);

        $question14 = new Question();
        $question14->setText('Quel mot-clé crée une nouvelle table?');
        $question14->setCorrectAnswer('CREATE TABLE');
        $question14->setQuiz($quiz3);
        $manager->persist($question14);

        $question15 = new Question();
        $question15->setText('Quel agrégat compte le nombre de lignes?');
        $question15->setCorrectAnswer('COUNT');
        $question15->setQuiz($quiz3);
        $manager->persist($question15);

        $manager->persist($quiz3);

        // Quiz 4: Symfony Framework
        $quiz4 = new Quiz();
        $quiz4->setTitle('Symfony Framework');
        $quiz4->setLevel('intermediaire');
        $quiz4->setDuration(25);

        $question16 = new Question();
        $question16->setText('Quel est le gestionnaire d\'entités Doctrine?');
        $question16->setCorrectAnswer('EntityManager');
        $question16->setQuiz($quiz4);
        $manager->persist($question16);

        $question17 = new Question();
        $question17->setText('Quel dossier contient les templates Twig?');
        $question17->setCorrectAnswer('templates');
        $question17->setQuiz($quiz4);
        $manager->persist($question17);

        $question18 = new Question();
        $question18->setText('Quel est le fichier config principal de Symfony?');
        $question18->setCorrectAnswer('services.yaml');
        $question18->setQuiz($quiz4);
        $manager->persist($question18);

        $question19 = new Question();
        $question19->setText('Comment générer une entité Doctrine?');
        $question19->setCorrectAnswer('make:entity');
        $question19->setQuiz($quiz4);
        $manager->persist($question19);

        $question20 = new Question();
        $question20->setText('Quel moteur de templates utilise Symfony?');
        $question20->setCorrectAnswer('Twig');
        $question20->setQuiz($quiz4);
        $manager->persist($question20);

        $manager->persist($quiz4);

        $manager->flush();
    }
}
