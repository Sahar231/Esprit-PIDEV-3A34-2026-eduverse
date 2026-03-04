<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add new fields to cours table: content, level, courseFile, courseVideo, courseImage
 */
final class Version20260212140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new fields to cours table: content, level, courseFile, courseVideo, courseImage';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cours ADD content LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE cours ADD level VARCHAR(50) NOT NULL DEFAULT "beginner"');
        $this->addSql('ALTER TABLE cours ADD course_file VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE cours ADD course_video VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE cours ADD course_image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cours DROP COLUMN content');
        $this->addSql('ALTER TABLE cours DROP COLUMN level');
        $this->addSql('ALTER TABLE cours DROP COLUMN course_file');
        $this->addSql('ALTER TABLE cours DROP COLUMN course_video');
        $this->addSql('ALTER TABLE cours DROP COLUMN course_image');
    }
}
