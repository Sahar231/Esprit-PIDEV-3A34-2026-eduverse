<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260210170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Approval workflow columns already exist - skipped';
    }

    public function up(Schema $schema): void
    {
        // Columns status, submitted_at, and rejection_reason 
        // should already be in the database from previous migrations
        // This is a placeholder migration to track that we intended to add them
    }

    public function down(Schema $schema): void
    {
        // Skip
    }
}
