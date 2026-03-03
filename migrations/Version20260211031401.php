<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211031401 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE quiz_attempt DROP FOREIGN KEY `FK_QUIZ_ATTEMPT`');
        $this->addSql('DROP TABLE quiz_attempt');
        $this->addSql('ALTER TABLE quiz ADD instructor_id INT DEFAULT NULL, DROP status, DROP creator_id, DROP creator_name, CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA928C4FC193 FOREIGN KEY (instructor_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_A412FA928C4FC193 ON quiz (instructor_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE quiz_attempt (id INT AUTO_INCREMENT NOT NULL, quiz_id INT NOT NULL, student_id INT NOT NULL, student_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, answers JSON NOT NULL, score INT NOT NULL, total_questions INT NOT NULL, attempted_at DATETIME NOT NULL, time_taken INT NOT NULL, INDEX FK_QUIZ_ATTEMPT (quiz_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE quiz_attempt ADD CONSTRAINT `FK_QUIZ_ATTEMPT` FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA928C4FC193');
        $this->addSql('DROP INDEX IDX_A412FA928C4FC193 ON quiz');
        $this->addSql('ALTER TABLE quiz ADD status VARCHAR(20) DEFAULT \'APPROVED\' NOT NULL, ADD creator_id INT DEFAULT 0 NOT NULL, ADD creator_name VARCHAR(100) DEFAULT \'system\' NOT NULL, DROP instructor_id, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
    }
}
