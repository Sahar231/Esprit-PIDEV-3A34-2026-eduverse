<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260211000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create test quiz with proper point values to validate scoring fix';
    }

    public function up(Schema $schema): void
    {
        // Note: This assumes there's already a user with id=1

        // Insert test formation if not exists
        $this->addSql(
            "INSERT IGNORE INTO formation (creator_id, title, description, content, price, is_approved, is_archived, created_at) 
             VALUES (?, 'Scoring Test Formation', 'Test formation to validate quiz scoring', 'Test content', 0, 1, 0, NOW())",
            [1]
        );

        // Get the formation ID
        $result = $this->connection->executeQuery(
            "SELECT id FROM formation WHERE title = 'Scoring Test Formation' LIMIT 1"
        )->fetchAssociative();
        
        if ($result) {
            $formationId = $result['id'];
            
            // Insert test quiz
            $this->addSql(
                "INSERT INTO quiz (Formation_id, Title, Description, Category, total_score, Pass_Score, Total_Questions, CreatedOn, Difficulty) 
                 VALUES (?, 'Scoring Test Quiz', 'Test quiz for validating correct scoring', 'Testing', 50, 70, 2, NOW(), 'easy')",
                [$formationId]
            );

            // Get the quiz ID
            $quizResult = $this->connection->executeQuery(
                "SELECT id FROM quiz WHERE Title = 'Scoring Test Quiz' LIMIT 1"
            )->fetchAssociative();
            
            if ($quizResult) {
                $quizId = $quizResult['id'];

                // Insert test questions with proper point values
                // Question 1: 25 points - MCQ with correct answer "Paris"
                $this->addSql(
                    "INSERT INTO question (quiz_id, question, reply, score, type, choices) 
                     VALUES (?, 'What is the capital of France?', 'Paris', 25, 'qcm', ?)",
                    [
                        $quizId,
                        json_encode(['Paris', 'London', 'Berlin', 'Madrid'])
                    ]
                );

                // Question 2: 25 points - MCQ with correct answer "PHP"
                $this->addSql(
                    "INSERT INTO question (quiz_id, question, reply, score, type, choices) 
                     VALUES (?, 'Which is a server-side programming language?', 'PHP', 25, 'qcm', ?)",
                    [
                        $quizId,
                        json_encode(['HTML', 'CSS', 'PHP', 'JavaScript'])
                    ]
                );
            }
        }
    }

    public function down(Schema $schema): void
    {
        // Remove test data
        $this->addSql("DELETE FROM question WHERE quiz_id IN (SELECT id FROM quiz WHERE Title = 'Scoring Test Quiz')");
        $this->addSql("DELETE FROM quiz WHERE Title = 'Scoring Test Quiz'");
        $this->addSql("DELETE FROM formation WHERE title = 'Scoring Test Formation'");
    }
}
