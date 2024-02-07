<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240207170341 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__webhook_responses AS SELECT id, webhook_id, status_code, body, headers, valid_until FROM webhook_responses');
        $this->addSql('DROP TABLE webhook_responses');
        $this->addSql('CREATE TABLE webhook_responses (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, webhook_id INTEGER NOT NULL, status_code INTEGER NOT NULL, body CLOB DEFAULT NULL, headers CLOB NOT NULL --(DC2Type:json)
        , valid_until DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_C0818DD35C9BA60B FOREIGN KEY (webhook_id) REFERENCES webhooks (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO webhook_responses (id, webhook_id, status_code, body, headers, valid_until) SELECT id, webhook_id, status_code, body, headers, valid_until FROM __temp__webhook_responses');
        $this->addSql('DROP TABLE __temp__webhook_responses');
        $this->addSql('CREATE INDEX IDX_414CDE85C9BA60B ON webhook_responses (webhook_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__webhooks AS SELECT id, user_id, url, method, body_expression, filter_expression, object_type, operation, headers, enhanced_filter, enabled, log_responses FROM webhooks');
        $this->addSql('DROP TABLE webhooks');
        $this->addSql('CREATE TABLE webhooks (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, url CLOB NOT NULL, method VARCHAR(10) NOT NULL, body_expression CLOB DEFAULT NULL, filter_expression CLOB DEFAULT NULL, object_type VARCHAR(180) NOT NULL, operation VARCHAR(180) DEFAULT NULL, headers CLOB DEFAULT NULL --(DC2Type:json)
        , enhanced_filter CLOB DEFAULT NULL, enabled BOOLEAN NOT NULL, log_responses BOOLEAN NOT NULL, CONSTRAINT FK_998C4FDDA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO webhooks (id, user_id, url, method, body_expression, filter_expression, object_type, operation, headers, enhanced_filter, enabled, log_responses) SELECT id, user_id, url, method, body_expression, filter_expression, object_type, operation, headers, enhanced_filter, enabled, log_responses FROM __temp__webhooks');
        $this->addSql('DROP TABLE __temp__webhooks');
        $this->addSql('CREATE INDEX IDX_998C4FDDA76ED395 ON webhooks (user_id)');
        $this->addSql('CREATE INDEX IDX_998C4FDD1981A66D ON webhooks (operation)');
        $this->addSql('CREATE INDEX IDX_998C4FDD11CB6B3A ON webhooks (object_type)');
        $this->addSql('CREATE INDEX IDX_998C4FDD50F9BB84 ON webhooks (enabled)');
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
        $this->addSql('CREATE INDEX IDX_C0818DD35C9BA60B ON webhook_responses (webhook_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__webhooks AS SELECT id, user_id, url, method, body_expression, filter_expression, object_type, operation, headers, enhanced_filter, enabled, log_responses FROM webhooks');
        $this->addSql('DROP TABLE webhooks');
        $this->addSql('CREATE TABLE webhooks (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, url CLOB NOT NULL, method VARCHAR(10) NOT NULL, body_expression CLOB DEFAULT NULL, filter_expression CLOB DEFAULT NULL, object_type VARCHAR(180) NOT NULL, operation VARCHAR(180) DEFAULT NULL, headers CLOB DEFAULT NULL --(DC2Type:json)
        , enhanced_filter CLOB DEFAULT NULL, enabled BOOLEAN NOT NULL, log_responses BOOLEAN DEFAULT false NOT NULL, CONSTRAINT FK_998C4FDDA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO webhooks (id, user_id, url, method, body_expression, filter_expression, object_type, operation, headers, enhanced_filter, enabled, log_responses) SELECT id, user_id, url, method, body_expression, filter_expression, object_type, operation, headers, enhanced_filter, enabled, log_responses FROM __temp__webhooks');
        $this->addSql('DROP TABLE __temp__webhooks');
        $this->addSql('CREATE INDEX IDX_998C4FDDA76ED395 ON webhooks (user_id)');
        $this->addSql('CREATE INDEX IDX_998C4FDD11CB6B3A ON webhooks (object_type)');
        $this->addSql('CREATE INDEX IDX_998C4FDD1981A66D ON webhooks (operation)');
        $this->addSql('CREATE INDEX IDX_998C4FDD50F9BB84 ON webhooks (enabled)');
    }
}
