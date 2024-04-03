<?php

namespace App\SqlObject;

use Doctrine\DBAL\Connection;

final readonly class LargePayloadTable implements InstallableSqlObject
{
    public function getName(): string
    {
        return 'rikudou_webhooks_large_payloads';
    }

    public function install(Connection $connection): void
    {
        $connection->executeStatement("create table if not exists {$this->getName()} (id serial primary key, payload text not null);");
    }

    public function uninstall(Connection $connection): void
    {
        $connection->executeStatement("drop table {$this->getName()}");
    }
}
