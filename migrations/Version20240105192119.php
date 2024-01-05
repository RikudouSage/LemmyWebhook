<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240105192119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE webhooks ADD COLUMN enhanced_filter CLOB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__webhooks AS SELECT id, url, method, body_expression, filter_expression, object_type, operation, headers FROM webhooks');
        $this->addSql('DROP TABLE webhooks');
        $this->addSql('CREATE TABLE webhooks (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, url CLOB NOT NULL, method VARCHAR(10) NOT NULL, body_expression CLOB DEFAULT NULL, filter_expression CLOB DEFAULT NULL, object_type VARCHAR(180) NOT NULL, operation VARCHAR(180) DEFAULT NULL, headers CLOB DEFAULT NULL --(DC2Type:json)
        )');
        $this->addSql('INSERT INTO webhooks (id, url, method, body_expression, filter_expression, object_type, operation, headers) SELECT id, url, method, body_expression, filter_expression, object_type, operation, headers FROM __temp__webhooks');
        $this->addSql('DROP TABLE __temp__webhooks');
    }
}
