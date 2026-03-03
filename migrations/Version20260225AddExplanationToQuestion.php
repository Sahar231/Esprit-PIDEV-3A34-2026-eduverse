<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260225AddExplanationToQuestion extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add explanation column to question table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE question ADD explanation VARCHAR(1000) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE question DROP explanation');
    }
}
