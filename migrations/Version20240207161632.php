<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240207161632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE webhook_response (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, webhook_id INTEGER NOT NULL, status_code INTEGER NOT NULL, body CLOB DEFAULT NULL, headers CLOB NOT NULL --(DC2Type:json)
        , valid_until DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_C0818DD35C9BA60B FOREIGN KEY (webhook_id) REFERENCES webhooks (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_C0818DD35C9BA60B ON webhook_response (webhook_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__refresh_tokens AS SELECT id, user_id, token, valid_until FROM refresh_tokens');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('CREATE TABLE refresh_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, token VARCHAR(180) NOT NULL, valid_until DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO refresh_tokens (id, user_id, token, valid_until) SELECT id, user_id, token, valid_until FROM __temp__refresh_tokens');
        $this->addSql('DROP TABLE __temp__refresh_tokens');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9BACE7E15F37A13B ON refresh_tokens (token)');
        $this->addSql('CREATE INDEX IDX_9BACE7E1A76ED395 ON refresh_tokens (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE webhook_response');
        $this->addSql('CREATE TEMPORARY TABLE __temp__refresh_tokens AS SELECT id, user_id, token, valid_until FROM refresh_tokens');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('CREATE TABLE refresh_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, token VARCHAR(180) NOT NULL, valid_until DATETIME DEFAULT NULL, CONSTRAINT FK_9BACE7E1A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO refresh_tokens (id, user_id, token, valid_until) SELECT id, user_id, token, valid_until FROM __temp__refresh_tokens');
        $this->addSql('DROP TABLE __temp__refresh_tokens');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9BACE7E15F37A13B ON refresh_tokens (token)');
        $this->addSql('CREATE INDEX IDX_9BACE7E1A76ED395 ON refresh_tokens (user_id)');
    }
}
