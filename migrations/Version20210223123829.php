<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210223123829 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sensors ADD category_id INT DEFAULT NULL, DROP category');
        $this->addSql('ALTER TABLE sensors ADD CONSTRAINT FK_D0D3FA9012469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('CREATE INDEX IDX_D0D3FA9012469DE2 ON sensors (category_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sensors DROP FOREIGN KEY FK_D0D3FA9012469DE2');
        $this->addSql('DROP INDEX IDX_D0D3FA9012469DE2 ON sensors');
        $this->addSql('ALTER TABLE sensors ADD category VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP category_id');
    }
}
