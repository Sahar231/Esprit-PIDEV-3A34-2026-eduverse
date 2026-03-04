<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260210000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add creator field to formation table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Check if column already exists before adding
        $schemaManager = $this->connection->createSchemaManager();
        $columns = $schemaManager->listTableColumns('formation');
        
        if (!isset($columns['creator_id'])) {
            $this->addSql('ALTER TABLE formation ADD creator_id INT');
            $this->addSql('ALTER TABLE formation ADD CONSTRAINT FK_404021BF61220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
            $this->addSql('CREATE INDEX IDX_404021BF61220EA6 ON formation (creator_id)');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE formation DROP FOREIGN KEY FK_404021BF61220EA6');
        $this->addSql('DROP INDEX IDX_404021BF61220EA6 ON formation');
        $this->addSql('ALTER TABLE formation DROP creator_id');
    }
}
