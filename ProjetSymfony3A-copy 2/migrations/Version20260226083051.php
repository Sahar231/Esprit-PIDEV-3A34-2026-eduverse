<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260226083051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_33D9E8F5858370E ON wallet_transaction');
        $this->addSql('ALTER TABLE wallet_transaction DROP FOREIGN KEY FK_33D9E8F5A76ED395');
        $this->addSql('ALTER TABLE wallet_transaction CHANGE created_at created_at DATETIME NOT NULL, CHANGE completed_at completed_at DATETIME DEFAULT NULL');
        $this->addSql('DROP INDEX idx_33d9e8f5a76ed395 ON wallet_transaction');
        $this->addSql('CREATE INDEX IDX_7DAF972A76ED395 ON wallet_transaction (user_id)');
        $this->addSql('ALTER TABLE wallet_transaction ADD CONSTRAINT FK_33D9E8F5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wallet_transaction DROP FOREIGN KEY FK_7DAF972A76ED395');
        $this->addSql('ALTER TABLE wallet_transaction CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE completed_at completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_33D9E8F5858370E ON wallet_transaction (stripe_session_id)');
        $this->addSql('DROP INDEX idx_7daf972a76ed395 ON wallet_transaction');
        $this->addSql('CREATE INDEX IDX_33D9E8F5A76ED395 ON wallet_transaction (user_id)');
        $this->addSql('ALTER TABLE wallet_transaction ADD CONSTRAINT FK_7DAF972A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }
}
