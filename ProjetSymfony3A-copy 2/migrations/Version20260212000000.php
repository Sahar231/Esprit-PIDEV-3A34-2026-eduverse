<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Fix courses and chapters schema
 */
final class Version20260212000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix courses and chapters schema to match entity definitions';
    }

    public function up(Schema $schema): void
    {
        // Fix chapitre table - update datetime types and add proper foreign keys
        $this->addSql('ALTER TABLE chapitre DROP FOREIGN KEY chapitre_ibfk_1');
        $this->addSql('ALTER TABLE chapitre DROP FOREIGN KEY chapitre_ibfk_2');
        $this->addSql('DROP INDEX idx_cours_id ON chapitre');
        $this->addSql('DROP INDEX idx_creator_id ON chapitre');
        
        $this->addSql('ALTER TABLE chapitre CHANGE created_at created_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)", CHANGE updated_at updated_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)"');
        
        $this->addSql('ALTER TABLE chapitre ADD CONSTRAINT FK_8C62B0257ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chapitre ADD CONSTRAINT FK_8C62B02561220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        
        $this->addSql('CREATE INDEX IDX_8C62B0257ECF78B0 ON chapitre (cours_id)');
        $this->addSql('CREATE INDEX IDX_8C62B02561220EA6 ON chapitre (creator_id)');

        // Fix cours table - update datetime types and add proper foreign keys
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY cours_ibfk_1');
        $this->addSql('DROP INDEX idx_status ON cours');
        $this->addSql('DROP INDEX idx_creator_id ON cours');
        $this->addSql('DROP INDEX idx_created_at ON cours');
        
        $this->addSql('ALTER TABLE cours CHANGE created_at created_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)", CHANGE updated_at updated_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)"');
        
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9C61220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        
        $this->addSql('CREATE INDEX IDX_FDCA8C9C61220EA6 ON cours (creator_id)');
        $this->addSql('CREATE INDEX IDX_FDCA8C9C7B00651C ON cours (status)');
        $this->addSql('CREATE INDEX IDX_FDCA8C9CB3FE509D ON cours (created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chapitre DROP FOREIGN KEY FK_8C62B0257ECF78B0');
        $this->addSql('ALTER TABLE chapitre DROP FOREIGN KEY FK_8C62B02561220EA6');
        $this->addSql('DROP INDEX IDX_8C62B0257ECF78B0 ON chapitre');
        $this->addSql('DROP INDEX IDX_8C62B02561220EA6 ON chapitre');
        $this->addSql('ALTER TABLE chapitre CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9C61220EA6');
        $this->addSql('DROP INDEX IDX_FDCA8C9C61220EA6 ON cours');
        $this->addSql('DROP INDEX IDX_FDCA8C9C7B00651C ON cours');
        $this->addSql('DROP INDEX IDX_FDCA8C9CB3FE509D ON cours');
        $this->addSql('ALTER TABLE cours CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
    }
}
