<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211033823 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE quiz_submission (id INT AUTO_INCREMENT NOT NULL, score INT NOT NULL, total INT NOT NULL, answers JSON NOT NULL, submitted_at DATETIME NOT NULL, quiz_id INT NOT NULL, student_id INT NOT NULL, INDEX IDX_926A7DCF853CD175 (quiz_id), INDEX IDX_926A7DCFCB944F1A (student_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE quiz_submission ADD CONSTRAINT FK_926A7DCF853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE quiz_submission ADD CONSTRAINT FK_926A7DCFCB944F1A FOREIGN KEY (student_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz_submission DROP FOREIGN KEY FK_926A7DCF853CD175');
        $this->addSql('ALTER TABLE quiz_submission DROP FOREIGN KEY FK_926A7DCFCB944F1A');
        $this->addSql('DROP TABLE quiz_submission');
    }
}
