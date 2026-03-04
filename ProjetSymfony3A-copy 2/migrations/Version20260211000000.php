<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20260211000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Calculate and set total_score for existing quizzes based on question scores';
    }

    public function up(Schema $schema): void
    {
        // For each quiz, calculate the sum of all question scores and update total_score
        $quizzes = $this->connection->executeQuery('SELECT id FROM quiz')->fetchAllAssociative();
        
        foreach ($quizzes as $quiz) {
            $quizId = $quiz['id'];
            
            // Sum all question scores for this quiz
            $result = $this->connection->executeQuery(
                'SELECT COALESCE(SUM(CAST(score AS DECIMAL(10,2))), 0) as total FROM question WHERE quiz_id = ?',
                [$quizId]
            )->fetchAssociative();
            
            $totalScore = (float)($result['total'] ?? 0);
            
            // Update quiz with calculated total_score
            $this->connection->executeStatement(
                'UPDATE quiz SET total_score = ? WHERE id = ?',
                [(string)$totalScore, $quizId]
            );
        }
    }

    public function down(Schema $schema): void
    {
        // This migration only populates data, which was missing.
        // Down migration would set scores back to 0, but that's not useful.
    }
}

