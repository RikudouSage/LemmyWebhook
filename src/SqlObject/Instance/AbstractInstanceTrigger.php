<?php

namespace App\SqlObject\Instance;

use App\SqlObject\AbstractTableTrigger;

abstract readonly class AbstractInstanceTrigger extends AbstractTableTrigger
{
    public function getTable(): string
    {
        return 'instance';
    }

    public function getFields(): array
    {
        return [
            'id',
            'domain',
            'software',
            'version',
        ];
    }
}
