<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260224084814 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE question DROP correct_answer');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY `FK_A412FA928C4FC193`');
        $this->addSql('ALTER TABLE quiz ADD description VARCHAR(1000) DEFAULT NULL, ADD submitted_at DATETIME DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE rejection_reason rejection_reason VARCHAR(1000) DEFAULT NULL');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA928C4FC193 FOREIGN KEY (instructor_id) REFERENCES `user` (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE question ADD correct_answer VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA928C4FC193');
        $this->addSql('ALTER TABLE quiz DROP description, DROP submitted_at, CHANGE created_at created_at DATETIME NOT NULL, CHANGE rejection_reason rejection_reason LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT `FK_A412FA928C4FC193` FOREIGN KEY (instructor_id) REFERENCES user (id)');
    }
}
