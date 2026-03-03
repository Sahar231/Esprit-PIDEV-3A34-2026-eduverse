<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260208164824 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE question ADD quiz_id INT NOT NULL, CHANGE correctanswer correct_answer VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('CREATE INDEX IDX_B6F7494E853CD175 ON question (quiz_id)');
        $this->addSql('ALTER TABLE reponse ADD student_answer VARCHAR(255) DEFAULT NULL, CHANGE question_id question_id INT NOT NULL, CHANGE studentanswer content VARCHAR(255) NOT NULL, CHANGE iscorrect is_correct TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E853CD175');
        $this->addSql('DROP INDEX IDX_B6F7494E853CD175 ON question');
        $this->addSql('ALTER TABLE question DROP quiz_id, CHANGE correct_answer correctanswer VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE reponse DROP student_answer, CHANGE question_id question_id INT DEFAULT NULL, CHANGE content studentanswer VARCHAR(255) NOT NULL, CHANGE is_correct iscorrect TINYINT NOT NULL');
    }
}
