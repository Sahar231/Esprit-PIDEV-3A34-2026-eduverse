<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260226033615 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ressources (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, type VARCHAR(255) NOT NULL, url VARCHAR(255) DEFAULT NULL, file_path VARCHAR(255) DEFAULT NULL, cost NUMERIC(10, 2) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, formation_id INT NOT NULL, INDEX IDX_6A2CD5C75200282E (formation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE ressources ADD CONSTRAINT FK_6A2CD5C75200282E FOREIGN KEY (formation_id) REFERENCES formation (id)');
        $this->addSql('ALTER TABLE wallet DROP first_name, DROP last_name');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ressources DROP FOREIGN KEY FK_6A2CD5C75200282E');
        $this->addSql('DROP TABLE ressources');
        $this->addSql('ALTER TABLE wallet ADD first_name VARCHAR(255) NOT NULL, ADD last_name VARCHAR(255) NOT NULL');
    }
}
