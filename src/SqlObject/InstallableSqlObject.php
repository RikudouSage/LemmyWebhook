<?php

namespace App\SqlObject;

use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.installable_sql')]
interface InstallableSqlObject
{
    public function getName(): string;
    public function install(Connection $connection): void;
    public function uninstall(Connection $connection): void;
}
