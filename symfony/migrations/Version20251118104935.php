<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251118104935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE S_admins (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(50) NOT NULL, password VARCHAR(255) NOT NULL, role_id INT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, UNIQUE INDEX UNIQ_783406BEF85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE S_countries (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, code VARCHAR(10) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE S_event_accreditations (id INT AUTO_INCREMENT NOT NULL, participant_type_id INT NOT NULL, event_id INT NOT NULL, created_by INT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE S_organizations (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE S_participant_types (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_142FC1C05E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE S_participants (id INT AUTO_INCREMENT NOT NULL, last_name VARCHAR(100) NOT NULL, first_name VARCHAR(100) NOT NULL, email VARCHAR(150) NOT NULL, qr_code VARCHAR(255) DEFAULT NULL, country VARCHAR(100) DEFAULT NULL, photo VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by INT NOT NULL, participant_type_id INT NOT NULL, UNIQUE INDEX UNIQ_AE97C338E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE S_programs (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, created_by INT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE S_registration_followups (id INT AUTO_INCREMENT NOT NULL, participant_id INT NOT NULL, step_id INT NOT NULL, status INT NOT NULL, created_by INT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE S_roles (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_2954FACEA750E8 (label), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE S_admins');
        $this->addSql('DROP TABLE S_countries');
        $this->addSql('DROP TABLE S_event_accreditations');
        $this->addSql('DROP TABLE S_organizations');
        $this->addSql('DROP TABLE S_participant_types');
        $this->addSql('DROP TABLE S_participants');
        $this->addSql('DROP TABLE S_programs');
        $this->addSql('DROP TABLE S_registration_followups');
        $this->addSql('DROP TABLE S_roles');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
