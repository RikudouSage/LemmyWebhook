<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240130162040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__authentication_tokens AS SELECT id, user_id, token, valid_until FROM authentication_tokens');
        $this->addSql('DROP TABLE authentication_tokens');
        $this->addSql('CREATE TABLE authentication_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, token VARCHAR(180) NOT NULL, valid_until DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO authentication_tokens (id, user_id, token, valid_until) SELECT id, user_id, token, valid_until FROM __temp__authentication_tokens');
        $this->addSql('DROP TABLE __temp__authentication_tokens');
        $this->addSql('CREATE INDEX IDX_E3D92D28A76ED395 ON authentication_tokens (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E3D92D285F37A13B ON authentication_tokens (token)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__refresh_tokens AS SELECT id, user_id, token FROM refresh_tokens');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('CREATE TABLE refresh_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, token VARCHAR(180) NOT NULL, FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO refresh_tokens (id, user_id, token) SELECT id, user_id, token FROM __temp__refresh_tokens');
        $this->addSql('DROP TABLE __temp__refresh_tokens');
        $this->addSql('CREATE INDEX IDX_9BACE7E1A76ED395 ON refresh_tokens (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9BACE7E15F37A13B ON refresh_tokens (token)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__scopes AS SELECT id, user_id, scope, granted FROM scopes');
        $this->addSql('DROP TABLE scopes');
        $this->addSql('CREATE TABLE scopes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, scope VARCHAR(180) NOT NULL, granted BOOLEAN NOT NULL, FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO scopes (id, user_id, scope, granted) SELECT id, user_id, scope, granted FROM __temp__scopes');
        $this->addSql('DROP TABLE __temp__scopes');
        $this->addSql('CREATE INDEX IDX_4D4E330A76ED395 ON scopes (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4D4E330A76ED395AF55D3 ON scopes (user_id, scope)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__users AS SELECT id, username, roles, password, enabled FROM users');
        $this->addSql('DROP TABLE users');
        $this->addSql('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, enabled BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO users (id, username, roles, password, enabled) SELECT id, username, roles, password, enabled FROM __temp__users');
        $this->addSql('DROP TABLE __temp__users');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9F85E0677 ON users (username)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__webhooks AS SELECT id, url, method, body_expression, filter_expression, object_type, operation, headers, enhanced_filter, enabled FROM webhooks');
        $this->addSql('DROP TABLE webhooks');
        $this->addSql('CREATE TABLE webhooks (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, url CLOB NOT NULL, method VARCHAR(10) NOT NULL, body_expression CLOB DEFAULT NULL, filter_expression CLOB DEFAULT NULL, object_type VARCHAR(180) NOT NULL, operation VARCHAR(180) DEFAULT NULL, headers CLOB DEFAULT NULL --(DC2Type:json)
        , enhanced_filter CLOB DEFAULT NULL, enabled BOOLEAN NOT NULL, CONSTRAINT FK_998C4FDDA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO webhooks (id, url, method, body_expression, filter_expression, object_type, operation, headers, enhanced_filter, enabled) SELECT id, url, method, body_expression, filter_expression, object_type, operation, headers, enhanced_filter, enabled FROM __temp__webhooks');
        $this->addSql('DROP TABLE __temp__webhooks');
        $this->addSql('CREATE INDEX IDX_998C4FDD50F9BB84 ON webhooks (enabled)');
        $this->addSql('CREATE INDEX IDX_998C4FDD11CB6B3A ON webhooks (object_type)');
        $this->addSql('CREATE INDEX IDX_998C4FDD1981A66D ON webhooks (operation)');
        $this->addSql('CREATE INDEX IDX_998C4FDDA76ED395 ON webhooks (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__authentication_tokens AS SELECT id, user_id, token, valid_until FROM authentication_tokens');
        $this->addSql('DROP TABLE authentication_tokens');
        $this->addSql('CREATE TABLE authentication_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, token VARCHAR(180) NOT NULL, valid_until DATETIME NOT NULL, CONSTRAINT FK_E3D92D28A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO authentication_tokens (id, user_id, token, valid_until) SELECT id, user_id, token, valid_until FROM __temp__authentication_tokens');
        $this->addSql('DROP TABLE __temp__authentication_tokens');
        $this->addSql('CREATE INDEX IDX_E3D92D28A76ED395 ON authentication_tokens (user_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__refresh_tokens AS SELECT id, user_id, token FROM refresh_tokens');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('CREATE TABLE refresh_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, token VARCHAR(180) NOT NULL, CONSTRAINT FK_9BACE7E1A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO refresh_tokens (id, user_id, token) SELECT id, user_id, token FROM __temp__refresh_tokens');
        $this->addSql('DROP TABLE __temp__refresh_tokens');
        $this->addSql('CREATE INDEX IDX_9BACE7E1A76ED395 ON refresh_tokens (user_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__scopes AS SELECT id, user_id, scope, granted FROM scopes');
        $this->addSql('DROP TABLE scopes');
        $this->addSql('CREATE TABLE scopes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, scope VARCHAR(180) NOT NULL, granted BOOLEAN DEFAULT false NOT NULL, CONSTRAINT FK_4D4E330A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO scopes (id, user_id, scope, granted) SELECT id, user_id, scope, granted FROM __temp__scopes');
        $this->addSql('DROP TABLE __temp__scopes');
        $this->addSql('CREATE INDEX IDX_4D4E330A76ED395 ON scopes (user_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__users AS SELECT id, username, roles, password, enabled FROM users');
        $this->addSql('DROP TABLE users');
        $this->addSql('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles CLOB NOT NULL, password VARCHAR(180) NOT NULL, enabled BOOLEAN DEFAULT false NOT NULL)');
        $this->addSql('INSERT INTO users (id, username, roles, password, enabled) SELECT id, username, roles, password, enabled FROM __temp__users');
        $this->addSql('DROP TABLE __temp__users');
        $this->addSql('CREATE TEMPORARY TABLE __temp__webhooks AS SELECT id, url, method, body_expression, filter_expression, object_type, operation, headers, enhanced_filter, enabled FROM webhooks');
        $this->addSql('DROP TABLE webhooks');
        $this->addSql('CREATE TABLE webhooks (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, url CLOB NOT NULL, method VARCHAR(10) NOT NULL, body_expression CLOB DEFAULT NULL, filter_expression CLOB DEFAULT NULL, object_type VARCHAR(180) NOT NULL, operation VARCHAR(180) DEFAULT NULL, headers CLOB DEFAULT NULL --(DC2Type:json)
        , enhanced_filter CLOB DEFAULT NULL, enabled BOOLEAN DEFAULT true NOT NULL)');
        $this->addSql('INSERT INTO webhooks (id, url, method, body_expression, filter_expression, object_type, operation, headers, enhanced_filter, enabled) SELECT id, url, method, body_expression, filter_expression, object_type, operation, headers, enhanced_filter, enabled FROM __temp__webhooks');
        $this->addSql('DROP TABLE __temp__webhooks');
        $this->addSql('CREATE INDEX IDX_998C4FDD11CB6B3A ON webhooks (object_type)');
        $this->addSql('CREATE INDEX IDX_998C4FDD1981A66D ON webhooks (operation)');
        $this->addSql('CREATE INDEX IDX_998C4FDD50F9BB84 ON webhooks (enabled)');
    }
}
