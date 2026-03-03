<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260210150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Skip - already handled by Version20260210170000';
    }

    public function up(Schema $schema): void
    {
        // Skip - columns handled elsewhere
    }

    public function down(Schema $schema): void
    {
        // Skip
    }
}
