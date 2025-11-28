<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251127070817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE s_programs ADD organization_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE s_programs ADD CONSTRAINT FK_882961B632C8A3DE FOREIGN KEY (organization_id) REFERENCES S_organizations (id)');
        $this->addSql('CREATE INDEX IDX_882961B632C8A3DE ON s_programs (organization_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE S_programs DROP FOREIGN KEY FK_882961B632C8A3DE');
        $this->addSql('DROP INDEX IDX_882961B632C8A3DE ON S_programs');
        $this->addSql('ALTER TABLE S_programs DROP organization_id');
    }
}
