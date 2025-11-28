<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251125091758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE s_events ADD CONSTRAINT FK_895344FB3EB8070A FOREIGN KEY (program_id) REFERENCES S_programs (id)');
        $this->addSql('CREATE INDEX IDX_895344FB3EB8070A ON s_events (program_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE S_events DROP FOREIGN KEY FK_895344FB3EB8070A');
        $this->addSql('DROP INDEX IDX_895344FB3EB8070A ON S_events');
    }
}
