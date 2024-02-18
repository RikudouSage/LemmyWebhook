<?php

namespace App\SqlObject\PrivateMessage;

use App\SqlObject\AbstractTableTrigger;

abstract readonly class AbstractPrivateMessageTrigger extends AbstractTableTrigger
{
    public function getTable(): string
    {
        return 'private_message';
    }

    public function getFields(): array
    {
        return [
            'id',
            'creator_id',
            'recipient_id',
            'local',
        ];
    }
}
