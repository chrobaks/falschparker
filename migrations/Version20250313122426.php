<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250313122426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE car_count (id INT AUTO_INCREMENT NOT NULL, user_id_id INT NOT NULL, tatbestand_id_id INT NOT NULL, street_name VARCHAR(255) NOT NULL, count INT NOT NULL, create_at DATETIME NOT NULL, INDEX IDX_5A72C1B59D86650F (user_id_id), INDEX IDX_5A72C1B51CE8EF73 (tatbestand_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE car_count ADD CONSTRAINT FK_5A72C1B59D86650F FOREIGN KEY (user_id_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE car_count ADD CONSTRAINT FK_5A72C1B51CE8EF73 FOREIGN KEY (tatbestand_id_id) REFERENCES tatbestand (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE car_count DROP FOREIGN KEY FK_5A72C1B59D86650F');
        $this->addSql('ALTER TABLE car_count DROP FOREIGN KEY FK_5A72C1B51CE8EF73');
        $this->addSql('DROP TABLE car_count');
    }
}
