<?php

namespace App\SqlObject;

use App\Enum\DatabaseOperation;
use Doctrine\DBAL\Connection;

abstract readonly class AbstractTableTrigger implements InstallableSqlObject, TableSpecificObject, DependentInstallableObject
{
    abstract protected function getTriggerOperation(): DatabaseOperation;

    /**
     * @return array<string>
     */
    abstract protected function getFields(): array;

    public function getDependencies(): array
    {
        return [
            BaseTriggerFunction::class,
        ];
    }

    public function uninstall(Connection $connection): void
    {
        $connection->executeStatement("drop trigger {$this->getName()} on {$this->getTable()}");
    }

    public function install(Connection $connection): void
    {
        $fields = "'" . implode("','", $this->getFields()) . "'";
        $connection->executeStatement(
            <<< SQL_CODE
            CREATE OR REPLACE TRIGGER {$this->getName()}
                AFTER {$this->getTriggerOperation()->value}
                ON {$this->getTable()}
                FOR EACH ROW
            EXECUTE PROCEDURE rikudou_notify_trigger({$fields});
            SQL_CODE
        );
    }
}
