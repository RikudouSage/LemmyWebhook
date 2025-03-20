<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250320145236 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE webhooks ADD COLUMN signing_mode VARCHAR(255) NOT NULL default 'none'");
        $this->addSql('ALTER TABLE webhooks ADD COLUMN signing_key CLOB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__webhooks AS SELECT id, user_id, url, method, body_expression, filter_expression, object_type, operation, headers, enhanced_filter, enabled, log_responses, unique_machine_name FROM webhooks');
        $this->addSql('DROP TABLE webhooks');
        $this->addSql('CREATE TABLE webhooks (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, url CLOB NOT NULL, method VARCHAR(10) NOT NULL, body_expression CLOB DEFAULT NULL, filter_expression CLOB DEFAULT NULL, object_type VARCHAR(180) NOT NULL, operation VARCHAR(180) DEFAULT NULL, headers CLOB DEFAULT NULL --(DC2Type:json)
        , enhanced_filter CLOB DEFAULT NULL, enabled BOOLEAN NOT NULL, log_responses BOOLEAN NOT NULL, unique_machine_name VARCHAR(180) DEFAULT NULL, CONSTRAINT FK_998C4FDDA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO webhooks (id, user_id, url, method, body_expression, filter_expression, object_type, operation, headers, enhanced_filter, enabled, log_responses, unique_machine_name) SELECT id, user_id, url, method, body_expression, filter_expression, object_type, operation, headers, enhanced_filter, enabled, log_responses, unique_machine_name FROM __temp__webhooks');
        $this->addSql('DROP TABLE __temp__webhooks');
        $this->addSql('CREATE INDEX IDX_998C4FDDA76ED395 ON webhooks (user_id)');
        $this->addSql('CREATE INDEX IDX_998C4FDD11CB6B3A ON webhooks (object_type)');
        $this->addSql('CREATE INDEX IDX_998C4FDD1981A66D ON webhooks (operation)');
        $this->addSql('CREATE INDEX IDX_998C4FDD50F9BB84 ON webhooks (enabled)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_998C4FDDA76ED3956154FA56 ON webhooks (user_id, unique_machine_name)');
    }
}
