<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240130161836 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('create table users
        (
            id       INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            username varchar(180)                      not null unique,
            roles    clob                              not null,
            password varchar(180)                      not null,
            enabled  BOOLEAN                           NOT NULL DEFAULT false
        )');
        $this->addSql('create table scopes
        (
            id      integer               not null primary key autoincrement,
            user_id integer               not null references users,
            scope   varchar(180)          not null,
            granted boolean default false not null,
            unique (scope, user_id)
        )');
        $this->addSql('create table refresh_tokens
        (
            id      integer               not null primary key autoincrement,
            token varchar(180) not null unique,
            user_id integer not null references users
        )');
        $this->addSql('create table authentication_tokens
        (
            id      integer               not null primary key autoincrement,
            token varchar(180) not null unique,
            valid_until datetime not null,
            user_id integer not null references users
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('drop table scopes');
        $this->addSql('drop table refresh_tokens');
        $this->addSql('drop table authentication_tokens');
        $this->addSql('drop table users');
    }
}
