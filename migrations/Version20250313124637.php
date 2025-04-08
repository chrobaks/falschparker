<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250313124637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE car_count DROP FOREIGN KEY FK_5A72C1B51CE8EF73');
        $this->addSql('ALTER TABLE car_count DROP FOREIGN KEY FK_5A72C1B59D86650F');
        $this->addSql('DROP INDEX IDX_5A72C1B51CE8EF73 ON car_count');
        $this->addSql('DROP INDEX IDX_5A72C1B59D86650F ON car_count');
        $this->addSql('ALTER TABLE car_count ADD user_id INT NOT NULL, ADD tatbestand_id INT NOT NULL, DROP user_id_id, DROP tatbestand_id_id');
        $this->addSql('ALTER TABLE car_count ADD CONSTRAINT FK_5A72C1B5A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE car_count ADD CONSTRAINT FK_5A72C1B5B9C54ACE FOREIGN KEY (tatbestand_id) REFERENCES tatbestand (id)');
        $this->addSql('CREATE INDEX IDX_5A72C1B5A76ED395 ON car_count (user_id)');
        $this->addSql('CREATE INDEX IDX_5A72C1B5B9C54ACE ON car_count (tatbestand_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE car_count DROP FOREIGN KEY FK_5A72C1B5A76ED395');
        $this->addSql('ALTER TABLE car_count DROP FOREIGN KEY FK_5A72C1B5B9C54ACE');
        $this->addSql('DROP INDEX IDX_5A72C1B5A76ED395 ON car_count');
        $this->addSql('DROP INDEX IDX_5A72C1B5B9C54ACE ON car_count');
        $this->addSql('ALTER TABLE car_count ADD user_id_id INT NOT NULL, ADD tatbestand_id_id INT NOT NULL, DROP user_id, DROP tatbestand_id');
        $this->addSql('ALTER TABLE car_count ADD CONSTRAINT FK_5A72C1B51CE8EF73 FOREIGN KEY (tatbestand_id_id) REFERENCES tatbestand (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE car_count ADD CONSTRAINT FK_5A72C1B59D86650F FOREIGN KEY (user_id_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_5A72C1B51CE8EF73 ON car_count (tatbestand_id_id)');
        $this->addSql('CREATE INDEX IDX_5A72C1B59D86650F ON car_count (user_id_id)');
    }
}
