<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260210AddTestQuizzes extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add test quizzes to the database';
    }

    public function up(Schema $schema): void
    {
        // Insert test quizzes
        $this->addSql("INSERT INTO quiz (title, level, duration) VALUES 
            ('Quiz Sécurité Informatique', 'intermediaire', 30),
            ('Quiz Marketing Digital', 'facile', 20),
            ('Quiz Python Avancé', 'difficile', 45)");

        // Get the inserted quiz IDs (this is a simple approach, might need adjustment)
        $this->addSql("SET @quiz1 = (SELECT id FROM quiz WHERE title = 'Quiz Sécurité Informatique' LIMIT 1)");
        $this->addSql("SET @quiz2 = (SELECT id FROM quiz WHERE title = 'Quiz Marketing Digital' LIMIT 1)");
        $this->addSql("SET @quiz3 = (SELECT id FROM quiz WHERE title = 'Quiz Python Avancé' LIMIT 1)");

        // Insert test questions for Quiz 1
        $this->addSql("INSERT INTO question (quiz_id, text, correct_answer) VALUES 
            (@quiz1, 'Qu''est-ce que la sécurité informatique ?', 'L''ensemble des mesures pour protéger les données')");

        // Insert test questions for Quiz 2
        $this->addSql("INSERT INTO question (quiz_id, text, correct_answer) VALUES 
            (@quiz2, 'Qu''est-ce que le SEO ?', 'Search Engine Optimization')");

        // Insert test questions for Quiz 3
        $this->addSql("INSERT INTO question (quiz_id, text, correct_answer) VALUES 
            (@quiz3, 'Qu''est-ce qu''un décorateur en Python ?', 'Une fonction qui modifie le comportement d''une autre fonction')");

        // Insert test responses
        $this->addSql("SET @q1 = (SELECT id FROM question WHERE text = 'Qu''est-ce que la sécurité informatique ?' LIMIT 1)");
        $this->addSql("INSERT INTO reponse (question_id, content, is_correct) VALUES 
            (@q1, 'L''ensemble des mesures pour protéger les données', 1),
            (@q1, 'Un type d''ordinateur', 0)");

        $this->addSql("SET @q2 = (SELECT id FROM question WHERE text = 'Qu''est-ce que le SEO ?' LIMIT 1)");
        $this->addSql("INSERT INTO reponse (question_id, content, is_correct) VALUES 
            (@q2, 'Search Engine Optimization', 1),
            (@q2, 'Social Engine Optimization', 0)");

        $this->addSql("SET @q3 = (SELECT id FROM question WHERE text = 'Qu''est-ce qu''un décorateur en Python ?' LIMIT 1)");
        $this->addSql("INSERT INTO reponse (question_id, content, is_correct) VALUES 
            (@q3, 'Une fonction qui modifie le comportement d''une autre fonction', 1),
            (@q3, 'Un type de variable', 0)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM reponse WHERE content IN ('L''ensemble des mesures pour protéger les données', 'Un type d''ordinateur', 'Search Engine Optimization', 'Social Engine Optimization', 'Une fonction qui modifie le comportement d''une autre fonction', 'Un type de variable')");
        $this->addSql("DELETE FROM question WHERE text IN ('Qu''est-ce que la sécurité informatique ?', 'Qu''est-ce que le SEO ?', 'Qu''est-ce qu''un décorateur en Python ?')");
        $this->addSql("DELETE FROM quiz WHERE title IN ('Quiz Sécurité Informatique', 'Quiz Marketing Digital', 'Quiz Python Avancé')");
    }
}
