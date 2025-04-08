<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250312170659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE carcount (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, tatbestand_id INT NOT NULL, street_name VARCHAR(255) NOT NULL, count INT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX IDX_139620D5A76ED395 (user_id), INDEX IDX_139620D5B9C54ACE (tatbestand_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE carcount ADD CONSTRAINT FK_139620D5A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE carcount ADD CONSTRAINT FK_139620D5B9C54ACE FOREIGN KEY (tatbestand_id) REFERENCES tatbestand (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE carcount DROP FOREIGN KEY FK_139620D5A76ED395');
        $this->addSql('ALTER TABLE carcount DROP FOREIGN KEY FK_139620D5B9C54ACE');
        $this->addSql('DROP TABLE carcount');
    }
}
