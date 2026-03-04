<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create cours and chapitre tables for course management system';
    }

    public function up(Schema $schema): void
    {
        // Create cours table
        $this->addSql('CREATE TABLE cours (
            id INT AUTO_INCREMENT NOT NULL,
            creator_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description LONGTEXT NOT NULL,
            category VARCHAR(100),
            status VARCHAR(50) NOT NULL DEFAULT "pending",
            created_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)",
            updated_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)",
            PRIMARY KEY(id),
            FOREIGN KEY (creator_id) REFERENCES user(id),
            INDEX idx_status (status),
            INDEX idx_creator_id (creator_id),
            INDEX idx_created_at (created_at)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');

        // Create chapitre table
        $this->addSql('CREATE TABLE chapitre (
            id INT AUTO_INCREMENT NOT NULL,
            cours_id INT NOT NULL,
            creator_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            content LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)",
            updated_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)",
            PRIMARY KEY(id),
            FOREIGN KEY (cours_id) REFERENCES cours(id) ON DELETE CASCADE,
            FOREIGN KEY (creator_id) REFERENCES user(id),
            INDEX idx_cours_id (cours_id),
            INDEX idx_creator_id (creator_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS chapitre');
        $this->addSql('DROP TABLE IF EXISTS cours');
    }
}
