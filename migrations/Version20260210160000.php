<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260210160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Skip - approval fields added in Version20260210170000';
    }

    public function up(Schema $schema): void
    {
        // Skip - see Version20260210170000
    }

    public function down(Schema $schema): void
    {
        // Skip
    }
}
