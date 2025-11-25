<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251121084952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE s_event_required_steps DROP FOREIGN KEY FK_3318992E71F7E88B');
        $this->addSql('ALTER TABLE s_event_required_steps DROP FOREIGN KEY FK_3318992E73B21E9C');
        $this->addSql('DROP TABLE s_event_required_steps');
        $this->addSql('ALTER TABLE s_participants CHANGE participant_type_id participant_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE s_participants ADD CONSTRAINT FK_AE97C3385F41439B FOREIGN KEY (participant_type_id) REFERENCES S_participant_types (id)');
        $this->addSql('CREATE INDEX IDX_AE97C3385F41439B ON s_participants (participant_type_id)');
        $this->addSql('ALTER TABLE s_registration_followups DROP FOREIGN KEY FK_31BDD1A471F7E88B');
        $this->addSql('ALTER TABLE s_registration_followups DROP FOREIGN KEY FK_31BDD1A4DE12AB56');
        $this->addSql('DROP INDEX IDX_31BDD1A471F7E88B ON s_registration_followups');
        $this->addSql('DROP INDEX IDX_31BDD1A4DE12AB56 ON s_registration_followups');
        $this->addSql('ALTER TABLE s_registration_followups CHANGE status status VARCHAR(255) NOT NULL, CHANGE event_id program_id INT NOT NULL');
        $this->addSql('ALTER TABLE s_registration_followups ADD CONSTRAINT FK_31BDD1A43EB8070A FOREIGN KEY (program_id) REFERENCES S_programs (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_31BDD1A43EB8070A ON s_registration_followups (program_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE s_event_required_steps (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, step_id INT NOT NULL, is_mandatory TINYINT(1) DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_3318992E71F7E88B (event_id), INDEX IDX_3318992E73B21E9C (step_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE s_event_required_steps ADD CONSTRAINT FK_3318992E71F7E88B FOREIGN KEY (event_id) REFERENCES s_events (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE s_event_required_steps ADD CONSTRAINT FK_3318992E73B21E9C FOREIGN KEY (step_id) REFERENCES s_registration_steps (id) ON UPDATE NO ACTION');
        $this->addSql('ALTER TABLE S_participants DROP FOREIGN KEY FK_AE97C3385F41439B');
        $this->addSql('DROP INDEX IDX_AE97C3385F41439B ON S_participants');
        $this->addSql('ALTER TABLE S_participants CHANGE participant_type_id participant_type_id INT NOT NULL');
        $this->addSql('ALTER TABLE S_registration_followups DROP FOREIGN KEY FK_31BDD1A43EB8070A');
        $this->addSql('DROP INDEX IDX_31BDD1A43EB8070A ON S_registration_followups');
        $this->addSql('ALTER TABLE S_registration_followups CHANGE status status INT NOT NULL, CHANGE program_id event_id INT NOT NULL');
        $this->addSql('ALTER TABLE S_registration_followups ADD CONSTRAINT FK_31BDD1A471F7E88B FOREIGN KEY (event_id) REFERENCES s_events (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE S_registration_followups ADD CONSTRAINT FK_31BDD1A4DE12AB56 FOREIGN KEY (created_by) REFERENCES s_admins (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_31BDD1A471F7E88B ON S_registration_followups (event_id)');
        $this->addSql('CREATE INDEX IDX_31BDD1A4DE12AB56 ON S_registration_followups (created_by)');
    }
}
