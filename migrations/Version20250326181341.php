<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250326181341 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tatbestand_count ADD tatbestand_id INT NOT NULL');
        $this->addSql('ALTER TABLE tatbestand_count ADD CONSTRAINT FK_E6753546B9C54ACE FOREIGN KEY (tatbestand_id) REFERENCES tatbestand (id)');
        $this->addSql('CREATE INDEX IDX_E6753546B9C54ACE ON tatbestand_count (tatbestand_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tatbestand_count DROP FOREIGN KEY FK_E6753546B9C54ACE');
        $this->addSql('DROP INDEX IDX_E6753546B9C54ACE ON tatbestand_count');
        $this->addSql('ALTER TABLE tatbestand_count DROP tatbestand_id');
    }
}
