<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251121071456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE S_event_required_steps (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, step_id INT NOT NULL, is_mandatory TINYINT(1) DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_3318992E71F7E88B (event_id), INDEX IDX_3318992E73B21E9C (step_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE S_event_required_steps ADD CONSTRAINT FK_3318992E71F7E88B FOREIGN KEY (event_id) REFERENCES S_events (id)');
        $this->addSql('ALTER TABLE S_event_required_steps ADD CONSTRAINT FK_3318992E73B21E9C FOREIGN KEY (step_id) REFERENCES S_registration_steps (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE event_required_steps DROP FOREIGN KEY FK_3D35A7B671F7E88B');
        $this->addSql('ALTER TABLE event_required_steps DROP FOREIGN KEY FK_3D35A7B673B21E9C');
        $this->addSql('DROP TABLE event_required_steps');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event_required_steps (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, step_id INT NOT NULL, is_mandatory TINYINT(1) DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_3D35A7B671F7E88B (event_id), INDEX IDX_3D35A7B673B21E9C (step_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE event_required_steps ADD CONSTRAINT FK_3D35A7B671F7E88B FOREIGN KEY (event_id) REFERENCES s_events (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE event_required_steps ADD CONSTRAINT FK_3D35A7B673B21E9C FOREIGN KEY (step_id) REFERENCES s_registration_steps (id) ON UPDATE NO ACTION');
        $this->addSql('ALTER TABLE S_event_required_steps DROP FOREIGN KEY FK_3318992E71F7E88B');
        $this->addSql('ALTER TABLE S_event_required_steps DROP FOREIGN KEY FK_3318992E73B21E9C');
        $this->addSql('DROP TABLE S_event_required_steps');
    }
}
