<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211165229 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE question_quiz (id INT AUTO_INCREMENT NOT NULL, question VARCHAR(255) NOT NULL, correct_answer VARCHAR(255) NOT NULL, score NUMERIC(10, 2) NOT NULL, type VARCHAR(50) NOT NULL, choices JSON DEFAULT NULL, quiz_id INT NOT NULL, INDEX IDX_FAFC177D853CD175 (quiz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE quiz_assessment (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, is_approved TINYINT(1) NOT NULL, state VARCHAR(100) DEFAULT NULL, level VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL, is_archived TINYINT(1) NOT NULL, creator_id INT NOT NULL, INDEX IDX_BE3D1A4C61220EA6 (creator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE quiz_resultat (id INT AUTO_INCREMENT NOT NULL, score NUMERIC(10, 2) NOT NULL, created_at DATETIME NOT NULL, answers JSON DEFAULT NULL, student_id INT NOT NULL, quiz_id INT NOT NULL, INDEX IDX_311FA4A7CB944F1A (student_id), INDEX IDX_311FA4A7853CD175 (quiz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE question_quiz ADD CONSTRAINT FK_FAFC177D853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz_assessment (id)');
        $this->addSql('ALTER TABLE quiz_assessment ADD CONSTRAINT FK_BE3D1A4C61220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE quiz_resultat ADD CONSTRAINT FK_311FA4A7CB944F1A FOREIGN KEY (student_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE quiz_resultat ADD CONSTRAINT FK_311FA4A7853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz_assessment (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE question_quiz DROP FOREIGN KEY FK_FAFC177D853CD175');
        $this->addSql('ALTER TABLE quiz_assessment DROP FOREIGN KEY FK_BE3D1A4C61220EA6');
        $this->addSql('ALTER TABLE quiz_resultat DROP FOREIGN KEY FK_311FA4A7CB944F1A');
        $this->addSql('ALTER TABLE quiz_resultat DROP FOREIGN KEY FK_311FA4A7853CD175');
        $this->addSql('DROP TABLE question_quiz');
        $this->addSql('DROP TABLE quiz_assessment');
        $this->addSql('DROP TABLE quiz_resultat');
    }
}
