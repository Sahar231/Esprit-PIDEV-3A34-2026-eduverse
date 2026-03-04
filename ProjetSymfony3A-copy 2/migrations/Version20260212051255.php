<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212051255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE club_members (club_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_48E8777D61190A32 (club_id), INDEX IDX_48E8777DA76ED395 (user_id), PRIMARY KEY(club_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, event_date DATETIME NOT NULL, location VARCHAR(255) DEFAULT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, club_id INT NOT NULL, creator_id INT NOT NULL, INDEX IDX_3BAE0AA761190A32 (club_id), INDEX IDX_3BAE0AA761220EA6 (creator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE join_request (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(50) NOT NULL, requested_at DATETIME NOT NULL, responded_at DATETIME DEFAULT NULL, club_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_E932E4FF61190A32 (club_id), INDEX IDX_E932E4FFA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE club_members ADD CONSTRAINT FK_48E8777D61190A32 FOREIGN KEY (club_id) REFERENCES club (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE club_members ADD CONSTRAINT FK_48E8777DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA761190A32 FOREIGN KEY (club_id) REFERENCES club (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA761220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE join_request ADD CONSTRAINT FK_E932E4FF61190A32 FOREIGN KEY (club_id) REFERENCES club (id)');
        $this->addSql('ALTER TABLE join_request ADD CONSTRAINT FK_E932E4FFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE club ADD description LONGTEXT DEFAULT NULL, ADD status VARCHAR(50) NOT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME DEFAULT NULL, ADD creator_id INT NOT NULL, DROP is_approved');
        $this->addSql('ALTER TABLE club ADD CONSTRAINT FK_B8EE387261220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_B8EE387261220EA6 ON club (creator_id)');
        $this->addSql('ALTER TABLE cours CHANGE level level VARCHAR(50) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club_members DROP FOREIGN KEY FK_48E8777D61190A32');
        $this->addSql('ALTER TABLE club_members DROP FOREIGN KEY FK_48E8777DA76ED395');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA761190A32');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA761220EA6');
        $this->addSql('ALTER TABLE join_request DROP FOREIGN KEY FK_E932E4FF61190A32');
        $this->addSql('ALTER TABLE join_request DROP FOREIGN KEY FK_E932E4FFA76ED395');
        $this->addSql('DROP TABLE club_members');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE join_request');
        $this->addSql('ALTER TABLE club DROP FOREIGN KEY FK_B8EE387261220EA6');
        $this->addSql('DROP INDEX IDX_B8EE387261220EA6 ON club');
        $this->addSql('ALTER TABLE club ADD is_approved TINYINT(1) NOT NULL, DROP description, DROP status, DROP created_at, DROP updated_at, DROP creator_id');
        $this->addSql('ALTER TABLE cours CHANGE level level VARCHAR(50) DEFAULT \'beginner\' NOT NULL');
    }
}
