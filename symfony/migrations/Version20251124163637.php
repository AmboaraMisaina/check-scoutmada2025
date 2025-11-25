<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251124163637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE S_program_registrations (id INT AUTO_INCREMENT NOT NULL, participant_id INT NOT NULL, program_id INT NOT NULL, created_by INT NOT NULL, INDEX IDX_84D77379D1C3019 (participant_id), INDEX IDX_84D77373EB8070A (program_id), UNIQUE INDEX unique_participant_program (participant_id, program_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE S_program_registrations ADD CONSTRAINT FK_84D77379D1C3019 FOREIGN KEY (participant_id) REFERENCES S_participants (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE S_program_registrations ADD CONSTRAINT FK_84D77373EB8070A FOREIGN KEY (program_id) REFERENCES S_programs (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE program_registrations DROP FOREIGN KEY FK_F697417F3EB8070A');
        $this->addSql('ALTER TABLE program_registrations DROP FOREIGN KEY FK_F697417F9D1C3019');
        $this->addSql('DROP TABLE program_registrations');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE program_registrations (id INT AUTO_INCREMENT NOT NULL, participant_id INT NOT NULL, program_id INT NOT NULL, created_by INT NOT NULL, UNIQUE INDEX unique_participant_program (participant_id, program_id), INDEX IDX_F697417F9D1C3019 (participant_id), INDEX IDX_F697417F3EB8070A (program_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE program_registrations ADD CONSTRAINT FK_F697417F3EB8070A FOREIGN KEY (program_id) REFERENCES s_programs (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE program_registrations ADD CONSTRAINT FK_F697417F9D1C3019 FOREIGN KEY (participant_id) REFERENCES s_participants (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE S_program_registrations DROP FOREIGN KEY FK_84D77379D1C3019');
        $this->addSql('ALTER TABLE S_program_registrations DROP FOREIGN KEY FK_84D77373EB8070A');
        $this->addSql('DROP TABLE S_program_registrations');
    }
}
