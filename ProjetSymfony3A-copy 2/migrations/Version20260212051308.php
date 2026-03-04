<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260212051308 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'This migration is a no-op because tables already exist from prior migrations';
    }

    public function up(Schema $schema): void
    {
        // Tables and constraints already exist from previous migrations
    }

    public function down(Schema $schema): void
    {
        // No-op
    }
}
