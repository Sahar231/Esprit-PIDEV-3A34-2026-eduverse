<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260211000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Increase decimal precision for question scores, quiz total_score, and pass_score';
    }

    public function up(Schema $schema): void
    {
        // Alter question.score field to allow larger values and decimals
        $this->addSql('ALTER TABLE question MODIFY score DECIMAL(10, 2)');
        
        // Alter quiz.total_score field to allow larger values
        $this->addSql('ALTER TABLE quiz MODIFY total_score DECIMAL(10, 2)');
        
        // Alter quiz.Pass_Score field to allow larger values (for percentage)
        $this->addSql('ALTER TABLE quiz MODIFY Pass_Score DECIMAL(5, 2)');
    }

    public function down(Schema $schema): void
    {
        // Reverse the changes - but this could lose data!
        $this->addSql('ALTER TABLE question MODIFY score DECIMAL(2, 2)');
        $this->addSql('ALTER TABLE quiz MODIFY total_score DECIMAL(2, 0)');
        $this->addSql('ALTER TABLE quiz MODIFY Pass_Score DECIMAL(2, 0)');
    }
}
