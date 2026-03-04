<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260210213116 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // First, set creator_id to 2 (admin) for any creator_id = 0
        $this->addSql('UPDATE formation SET creator_id = 2 WHERE creator_id = 0 OR creator_id IS NULL');
        
        // Now add the foreign key constraint
        $this->addSql('ALTER TABLE formation ADD CONSTRAINT FK_404021BF61220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_404021BF61220EA6 ON formation (creator_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE formation DROP FOREIGN KEY FK_404021BF61220EA6');
        $this->addSql('DROP INDEX IDX_404021BF61220EA6 ON formation');
    }
}
