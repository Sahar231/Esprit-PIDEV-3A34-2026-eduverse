<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260224120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add description column to quiz table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE quiz ADD COLUMN IF NOT EXISTS description VARCHAR(1000) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE quiz DROP COLUMN IF EXISTS description');
    }
}
