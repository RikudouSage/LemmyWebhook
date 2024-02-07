<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240207171143 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE webhook_responses ADD COLUMN created DATETIME NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__webhook_responses AS SELECT id, webhook_id, status_code, body, headers, valid_until FROM webhook_responses');
        $this->addSql('DROP TABLE webhook_responses');
        $this->addSql('CREATE TABLE webhook_responses (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, webhook_id INTEGER NOT NULL, status_code INTEGER NOT NULL, body CLOB DEFAULT NULL, headers CLOB NOT NULL --(DC2Type:json)
        , valid_until DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_414CDE85C9BA60B FOREIGN KEY (webhook_id) REFERENCES webhooks (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO webhook_responses (id, webhook_id, status_code, body, headers, valid_until) SELECT id, webhook_id, status_code, body, headers, valid_until FROM __temp__webhook_responses');
        $this->addSql('DROP TABLE __temp__webhook_responses');
        $this->addSql('CREATE INDEX IDX_414CDE85C9BA60B ON webhook_responses (webhook_id)');
    }
}
