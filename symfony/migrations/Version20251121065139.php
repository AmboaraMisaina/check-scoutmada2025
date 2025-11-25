<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251121065139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE S_admins (id INT AUTO_INCREMENT NOT NULL, role_id INT NOT NULL, username VARCHAR(50) NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, UNIQUE INDEX UNIQ_783406BEF85E0677 (username), INDEX IDX_783406BED60322AC (role_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE S_badge_templates (id INT AUTO_INCREMENT NOT NULL, organization_id INT NOT NULL, name VARCHAR(255) NOT NULL, file_path VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_1F86D81332C8A3DE (organization_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE S_countries (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, code VARCHAR(10) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE S_event_accreditations (id INT AUTO_INCREMENT NOT NULL, participant_type_id INT NOT NULL, event_id INT NOT NULL, created_by INT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_4254E4D65F41439B (participant_type_id), INDEX IDX_4254E4D671F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE S_events (id INT AUTO_INCREMENT NOT NULL, organization_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, created_by INT NOT NULL, updated_by INT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_895344FB32C8A3DE (organization_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE S_organizations (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE S_participant_types (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_142FC1C05E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE S_participants (id INT AUTO_INCREMENT NOT NULL, last_name VARCHAR(100) NOT NULL, first_name VARCHAR(100) NOT NULL, email VARCHAR(150) NOT NULL, qr_code VARCHAR(255) DEFAULT NULL, country VARCHAR(100) DEFAULT NULL, photo VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by INT NOT NULL, participant_type_id INT NOT NULL, UNIQUE INDEX UNIQ_AE97C338E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE S_programs (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, created_by INT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE S_registration_followups (id INT AUTO_INCREMENT NOT NULL, participant_id INT NOT NULL, step_id INT NOT NULL, event_id INT NOT NULL, created_by INT NOT NULL, status INT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX IDX_31BDD1A49D1C3019 (participant_id), INDEX IDX_31BDD1A473B21E9C (step_id), INDEX IDX_31BDD1A471F7E88B (event_id), INDEX IDX_31BDD1A4DE12AB56 (created_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE S_registration_steps (id INT AUTO_INCREMENT NOT NULL, organization_id INT NOT NULL, step VARCHAR(255) NOT NULL, step_order INT DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, created_by INT NOT NULL, updated_by INT NOT NULL, INDEX IDX_B404F94F32C8A3DE (organization_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE S_registrations (id INT AUTO_INCREMENT NOT NULL, participant_id INT NOT NULL, program_id INT NOT NULL, created_by INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_6502E5A29D1C3019 (participant_id), INDEX IDX_6502E5A23EB8070A (program_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE S_roles (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_2954FACEA750E8 (label), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE badge_fields (id INT AUTO_INCREMENT NOT NULL, badge_template_id INT NOT NULL, field_name VARCHAR(100) NOT NULL, pos_x INT NOT NULL, pos_y INT NOT NULL, font_size INT DEFAULT NULL, font_color VARCHAR(20) DEFAULT NULL, width INT DEFAULT NULL, height INT DEFAULT NULL, INDEX IDX_4C5940CDEB233D6F (badge_template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_required_steps (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, step_id INT NOT NULL, is_mandatory TINYINT(1) DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_3D35A7B671F7E88B (event_id), INDEX IDX_3D35A7B673B21E9C (step_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE S_admins ADD CONSTRAINT FK_783406BED60322AC FOREIGN KEY (role_id) REFERENCES S_roles (id)');
        $this->addSql('ALTER TABLE S_badge_templates ADD CONSTRAINT FK_1F86D81332C8A3DE FOREIGN KEY (organization_id) REFERENCES S_organizations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE S_event_accreditations ADD CONSTRAINT FK_4254E4D65F41439B FOREIGN KEY (participant_type_id) REFERENCES S_participant_types (id)');
        $this->addSql('ALTER TABLE S_event_accreditations ADD CONSTRAINT FK_4254E4D671F7E88B FOREIGN KEY (event_id) REFERENCES S_events (id)');
        $this->addSql('ALTER TABLE S_events ADD CONSTRAINT FK_895344FB32C8A3DE FOREIGN KEY (organization_id) REFERENCES S_organizations (id)');
        $this->addSql('ALTER TABLE S_registration_followups ADD CONSTRAINT FK_31BDD1A49D1C3019 FOREIGN KEY (participant_id) REFERENCES S_participants (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE S_registration_followups ADD CONSTRAINT FK_31BDD1A473B21E9C FOREIGN KEY (step_id) REFERENCES S_registration_steps (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE S_registration_followups ADD CONSTRAINT FK_31BDD1A471F7E88B FOREIGN KEY (event_id) REFERENCES S_events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE S_registration_followups ADD CONSTRAINT FK_31BDD1A4DE12AB56 FOREIGN KEY (created_by) REFERENCES S_admins (id)');
        $this->addSql('ALTER TABLE S_registration_steps ADD CONSTRAINT FK_B404F94F32C8A3DE FOREIGN KEY (organization_id) REFERENCES S_organizations (id)');
        $this->addSql('ALTER TABLE S_registrations ADD CONSTRAINT FK_6502E5A29D1C3019 FOREIGN KEY (participant_id) REFERENCES S_participants (id)');
        $this->addSql('ALTER TABLE S_registrations ADD CONSTRAINT FK_6502E5A23EB8070A FOREIGN KEY (program_id) REFERENCES S_programs (id)');
        $this->addSql('ALTER TABLE badge_fields ADD CONSTRAINT FK_4C5940CDEB233D6F FOREIGN KEY (badge_template_id) REFERENCES S_badge_templates (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_required_steps ADD CONSTRAINT FK_3D35A7B671F7E88B FOREIGN KEY (event_id) REFERENCES S_events (id)');
        $this->addSql('ALTER TABLE event_required_steps ADD CONSTRAINT FK_3D35A7B673B21E9C FOREIGN KEY (step_id) REFERENCES S_registration_steps (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE S_admins DROP FOREIGN KEY FK_783406BED60322AC');
        $this->addSql('ALTER TABLE S_badge_templates DROP FOREIGN KEY FK_1F86D81332C8A3DE');
        $this->addSql('ALTER TABLE S_event_accreditations DROP FOREIGN KEY FK_4254E4D65F41439B');
        $this->addSql('ALTER TABLE S_event_accreditations DROP FOREIGN KEY FK_4254E4D671F7E88B');
        $this->addSql('ALTER TABLE S_events DROP FOREIGN KEY FK_895344FB32C8A3DE');
        $this->addSql('ALTER TABLE S_registration_followups DROP FOREIGN KEY FK_31BDD1A49D1C3019');
        $this->addSql('ALTER TABLE S_registration_followups DROP FOREIGN KEY FK_31BDD1A473B21E9C');
        $this->addSql('ALTER TABLE S_registration_followups DROP FOREIGN KEY FK_31BDD1A471F7E88B');
        $this->addSql('ALTER TABLE S_registration_followups DROP FOREIGN KEY FK_31BDD1A4DE12AB56');
        $this->addSql('ALTER TABLE S_registration_steps DROP FOREIGN KEY FK_B404F94F32C8A3DE');
        $this->addSql('ALTER TABLE S_registrations DROP FOREIGN KEY FK_6502E5A29D1C3019');
        $this->addSql('ALTER TABLE S_registrations DROP FOREIGN KEY FK_6502E5A23EB8070A');
        $this->addSql('ALTER TABLE badge_fields DROP FOREIGN KEY FK_4C5940CDEB233D6F');
        $this->addSql('ALTER TABLE event_required_steps DROP FOREIGN KEY FK_3D35A7B671F7E88B');
        $this->addSql('ALTER TABLE event_required_steps DROP FOREIGN KEY FK_3D35A7B673B21E9C');
        $this->addSql('DROP TABLE S_admins');
        $this->addSql('DROP TABLE S_badge_templates');
        $this->addSql('DROP TABLE S_countries');
        $this->addSql('DROP TABLE S_event_accreditations');
        $this->addSql('DROP TABLE S_events');
        $this->addSql('DROP TABLE S_organizations');
        $this->addSql('DROP TABLE S_participant_types');
        $this->addSql('DROP TABLE S_participants');
        $this->addSql('DROP TABLE S_programs');
        $this->addSql('DROP TABLE S_registration_followups');
        $this->addSql('DROP TABLE S_registration_steps');
        $this->addSql('DROP TABLE S_registrations');
        $this->addSql('DROP TABLE S_roles');
        $this->addSql('DROP TABLE badge_fields');
        $this->addSql('DROP TABLE event_required_steps');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
