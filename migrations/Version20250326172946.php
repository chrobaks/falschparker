<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250326172946 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tatbestand_count (id INT AUTO_INCREMENT NOT NULL, car_count_id INT NOT NULL, count INT NOT NULL, INDEX IDX_E6753546C93E0F8E (car_count_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tatbestand_count ADD CONSTRAINT FK_E6753546C93E0F8E FOREIGN KEY (car_count_id) REFERENCES car_count (id)');
        $this->addSql('ALTER TABLE car_count DROP FOREIGN KEY FK_5A72C1B5B9C54ACE');
        $this->addSql('DROP INDEX IDX_5A72C1B5B9C54ACE ON car_count');
        $this->addSql('ALTER TABLE car_count DROP tatbestand_id, DROP count, CHANGE create_at create_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tatbestand_count DROP FOREIGN KEY FK_E6753546C93E0F8E');
        $this->addSql('DROP TABLE tatbestand_count');
        $this->addSql('ALTER TABLE car_count ADD tatbestand_id INT NOT NULL, ADD count INT NOT NULL, CHANGE create_at create_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE car_count ADD CONSTRAINT FK_5A72C1B5B9C54ACE FOREIGN KEY (tatbestand_id) REFERENCES tatbestand (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_5A72C1B5B9C54ACE ON car_count (tatbestand_id)');
    }
}
