<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260210180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Skip - approval workflow columns already exist from previous migrations';
    }

    public function up(Schema $schema): void
    {
        // Columns status, submitted_at, and rejection_reason already exist
        // This migration is skipped to avoid duplicate column errors
    }

    public function down(Schema $schema): void
    {
        // Skip
    }
}
