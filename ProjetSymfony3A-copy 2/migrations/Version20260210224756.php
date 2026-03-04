<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260210224756 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_FEEDFDC65200282E');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_FEEDFDC6853CD175');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_FEEDFDC6A76ED395');
        $this->addSql('ALTER TABLE certificate CHANGE awarded_at awarded_at DATETIME NOT NULL');
        $this->addSql('DROP INDEX idx_feedfdc6a76ed395 ON certificate');
        $this->addSql('CREATE INDEX IDX_219CDA4AA76ED395 ON certificate (user_id)');
        $this->addSql('DROP INDEX idx_feedfdc65200282e ON certificate');
        $this->addSql('CREATE INDEX IDX_219CDA4A5200282E ON certificate (formation_id)');
        $this->addSql('DROP INDEX idx_feedfdc6853cd175 ON certificate');
        $this->addSql('CREATE INDEX IDX_219CDA4A853CD175 ON certificate (quiz_id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_FEEDFDC65200282E FOREIGN KEY (formation_id) REFERENCES formation (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_FEEDFDC6853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_FEEDFDC6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4AA76ED395');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4A5200282E');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4A853CD175');
        $this->addSql('ALTER TABLE certificate CHANGE awarded_at awarded_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('DROP INDEX idx_219cda4a853cd175 ON certificate');
        $this->addSql('CREATE INDEX IDX_FEEDFDC6853CD175 ON certificate (quiz_id)');
        $this->addSql('DROP INDEX idx_219cda4aa76ed395 ON certificate');
        $this->addSql('CREATE INDEX IDX_FEEDFDC6A76ED395 ON certificate (user_id)');
        $this->addSql('DROP INDEX idx_219cda4a5200282e ON certificate');
        $this->addSql('CREATE INDEX IDX_FEEDFDC65200282E ON certificate (formation_id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4A5200282E FOREIGN KEY (formation_id) REFERENCES formation (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4A853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
    }
}
