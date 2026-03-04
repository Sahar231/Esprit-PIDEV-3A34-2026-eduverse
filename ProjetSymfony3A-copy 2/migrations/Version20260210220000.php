<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260210220000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create certificate table for badges and achievements';
    }

    public function up(Schema $schema): void
    {
        // Create certificate table
        $this->addSql('CREATE TABLE IF NOT EXISTS certificate (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, formation_id INT NOT NULL, quiz_id INT, score DOUBLE PRECISION NOT NULL, awarded_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_FEEDFDC6A76ED395 (user_id), INDEX IDX_FEEDFDC65200282E (formation_id), INDEX IDX_FEEDFDC6853CD175 (quiz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_FEEDFDC6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_FEEDFDC65200282E FOREIGN KEY (formation_id) REFERENCES formation (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_FEEDFDC6853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
    }

    public function down(Schema $schema): void
    {
        // Drop certificate table
        $this->addSql('DROP TABLE IF EXISTS certificate');
    }
}
