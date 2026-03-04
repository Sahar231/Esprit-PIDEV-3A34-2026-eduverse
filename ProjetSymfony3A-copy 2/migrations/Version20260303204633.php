<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303204633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ressources (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, type VARCHAR(255) NOT NULL, url VARCHAR(255) DEFAULT NULL, file_path VARCHAR(255) DEFAULT NULL, cost NUMERIC(10, 2) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, formation_id INT NOT NULL, INDEX IDX_6A2CD5C75200282E (formation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE wallet_transaction (id INT AUTO_INCREMENT NOT NULL, credits INT NOT NULL, amount NUMERIC(10, 2) NOT NULL, status VARCHAR(50) NOT NULL, stripe_session_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, completed_at DATETIME DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_7DAF972A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE ressources ADD CONSTRAINT FK_6A2CD5C75200282E FOREIGN KEY (formation_id) REFERENCES formation (id)');
        $this->addSql('ALTER TABLE wallet_transaction ADD CONSTRAINT FK_7DAF972A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE certificate CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4A5200282E FOREIGN KEY (formation_id) REFERENCES formation (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4A853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE chapitre CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE chapitre ADD CONSTRAINT FK_8C62B0257ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id)');
        $this->addSql('ALTER TABLE chapitre ADD CONSTRAINT FK_8C62B02561220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE club CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE club ADD CONSTRAINT FK_B8EE387261220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE club_members ADD CONSTRAINT FK_48E8777D61190A32 FOREIGN KEY (club_id) REFERENCES club (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE club_members ADD CONSTRAINT FK_48E8777DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cours CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9C61220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE course CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE evaluation CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE event CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA761190A32 FOREIGN KEY (club_id) REFERENCES club (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA761220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE formation CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE formation ADD CONSTRAINT FK_404021BF61220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE join_request CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE join_request ADD CONSTRAINT FK_E932E4FF61190A32 FOREIGN KEY (club_id) REFERENCES club (id)');
        $this->addSql('ALTER TABLE join_request ADD CONSTRAINT FK_E932E4FFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE question CHANGE id id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('CREATE INDEX IDX_B6F7494E853CD175 ON question (quiz_id)');
        $this->addSql('ALTER TABLE question_quiz CHANGE id id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE question_quiz ADD CONSTRAINT FK_FAFC177D853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz_assessment (id)');
        $this->addSql('CREATE INDEX IDX_FAFC177D853CD175 ON question_quiz (quiz_id)');
        $this->addSql('ALTER TABLE quiz CHANGE id id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA925200282E FOREIGN KEY (formation_id) REFERENCES formation (id)');
        $this->addSql('CREATE INDEX IDX_A412FA925200282E ON quiz (formation_id)');
        $this->addSql('ALTER TABLE quiz_assessment CHANGE id id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE quiz_assessment ADD CONSTRAINT FK_BE3D1A4C61220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_BE3D1A4C61220EA6 ON quiz_assessment (creator_id)');
        $this->addSql('ALTER TABLE quiz_resultat CHANGE id id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE quiz_resultat ADD CONSTRAINT FK_311FA4A7CB944F1A FOREIGN KEY (student_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE quiz_resultat ADD CONSTRAINT FK_311FA4A7853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz_assessment (id)');
        $this->addSql('CREATE INDEX IDX_311FA4A7CB944F1A ON quiz_resultat (student_id)');
        $this->addSql('CREATE INDEX IDX_311FA4A7853CD175 ON quiz_resultat (quiz_id)');
        $this->addSql('ALTER TABLE reset_password_request CHANGE id id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_7CE748AA76ED395 ON reset_password_request (user_id)');
        $this->addSql('ALTER TABLE user ADD is_two_factor_enabled TINYINT(1) DEFAULT 0 NOT NULL, ADD phone_number VARCHAR(20) DEFAULT NULL, ADD two_factor_code VARCHAR(6) DEFAULT NULL, ADD two_factor_expires_at DATETIME DEFAULT NULL, ADD face_descriptor JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE user_formation ADD PRIMARY KEY (user_id, formation_id)');
        $this->addSql('ALTER TABLE user_formation ADD CONSTRAINT FK_40A0AC5BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_formation ADD CONSTRAINT FK_40A0AC5B5200282E FOREIGN KEY (formation_id) REFERENCES formation (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_40A0AC5BA76ED395 ON user_formation (user_id)');
        $this->addSql('CREATE INDEX IDX_40A0AC5B5200282E ON user_formation (formation_id)');
        $this->addSql('ALTER TABLE wallet DROP first_name, DROP last_name, CHANGE id id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE wallet ADD CONSTRAINT FK_7C68921FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7C68921FA76ED395 ON wallet (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ressources DROP FOREIGN KEY FK_6A2CD5C75200282E');
        $this->addSql('ALTER TABLE wallet_transaction DROP FOREIGN KEY FK_7DAF972A76ED395');
        $this->addSql('DROP TABLE ressources');
        $this->addSql('DROP TABLE wallet_transaction');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4AA76ED395');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4A5200282E');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4A853CD175');
        $this->addSql('ALTER TABLE certificate CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE chapitre DROP FOREIGN KEY FK_8C62B0257ECF78B0');
        $this->addSql('ALTER TABLE chapitre DROP FOREIGN KEY FK_8C62B02561220EA6');
        $this->addSql('ALTER TABLE chapitre CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE club DROP FOREIGN KEY FK_B8EE387261220EA6');
        $this->addSql('ALTER TABLE club CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE club_members DROP FOREIGN KEY FK_48E8777D61190A32');
        $this->addSql('ALTER TABLE club_members DROP FOREIGN KEY FK_48E8777DA76ED395');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9C61220EA6');
        $this->addSql('ALTER TABLE cours CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE course CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE evaluation CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA761190A32');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA761220EA6');
        $this->addSql('ALTER TABLE event CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE formation DROP FOREIGN KEY FK_404021BF61220EA6');
        $this->addSql('ALTER TABLE formation CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE join_request DROP FOREIGN KEY FK_E932E4FF61190A32');
        $this->addSql('ALTER TABLE join_request DROP FOREIGN KEY FK_E932E4FFA76ED395');
        $this->addSql('ALTER TABLE join_request CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE question MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E853CD175');
        $this->addSql('DROP INDEX IDX_B6F7494E853CD175 ON question');
        $this->addSql('DROP INDEX `primary` ON question');
        $this->addSql('ALTER TABLE question CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE question_quiz MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE question_quiz DROP FOREIGN KEY FK_FAFC177D853CD175');
        $this->addSql('DROP INDEX IDX_FAFC177D853CD175 ON question_quiz');
        $this->addSql('DROP INDEX `primary` ON question_quiz');
        $this->addSql('ALTER TABLE question_quiz CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE quiz MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA925200282E');
        $this->addSql('DROP INDEX IDX_A412FA925200282E ON quiz');
        $this->addSql('DROP INDEX `primary` ON quiz');
        $this->addSql('ALTER TABLE quiz CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE quiz_assessment MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE quiz_assessment DROP FOREIGN KEY FK_BE3D1A4C61220EA6');
        $this->addSql('DROP INDEX IDX_BE3D1A4C61220EA6 ON quiz_assessment');
        $this->addSql('DROP INDEX `primary` ON quiz_assessment');
        $this->addSql('ALTER TABLE quiz_assessment CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE quiz_resultat MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE quiz_resultat DROP FOREIGN KEY FK_311FA4A7CB944F1A');
        $this->addSql('ALTER TABLE quiz_resultat DROP FOREIGN KEY FK_311FA4A7853CD175');
        $this->addSql('DROP INDEX IDX_311FA4A7CB944F1A ON quiz_resultat');
        $this->addSql('DROP INDEX IDX_311FA4A7853CD175 ON quiz_resultat');
        $this->addSql('DROP INDEX `primary` ON quiz_resultat');
        $this->addSql('ALTER TABLE quiz_resultat CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE reset_password_request MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('DROP INDEX IDX_7CE748AA76ED395 ON reset_password_request');
        $this->addSql('DROP INDEX `primary` ON reset_password_request');
        $this->addSql('ALTER TABLE reset_password_request CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE user DROP is_two_factor_enabled, DROP phone_number, DROP two_factor_code, DROP two_factor_expires_at, DROP face_descriptor');
        $this->addSql('ALTER TABLE user_formation DROP FOREIGN KEY FK_40A0AC5BA76ED395');
        $this->addSql('ALTER TABLE user_formation DROP FOREIGN KEY FK_40A0AC5B5200282E');
        $this->addSql('DROP INDEX IDX_40A0AC5BA76ED395 ON user_formation');
        $this->addSql('DROP INDEX IDX_40A0AC5B5200282E ON user_formation');
        $this->addSql('DROP INDEX `primary` ON user_formation');
        $this->addSql('ALTER TABLE wallet MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE wallet DROP FOREIGN KEY FK_7C68921FA76ED395');
        $this->addSql('DROP INDEX UNIQ_7C68921FA76ED395 ON wallet');
        $this->addSql('DROP INDEX `primary` ON wallet');
        $this->addSql('ALTER TABLE wallet ADD first_name VARCHAR(255) NOT NULL, ADD last_name VARCHAR(255) NOT NULL, CHANGE id id INT NOT NULL');
    }
}
