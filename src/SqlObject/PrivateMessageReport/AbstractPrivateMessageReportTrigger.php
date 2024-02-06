<?php

namespace App\SqlObject\PrivateMessageReport;

use App\SqlObject\AbstractTableTrigger;

abstract readonly class AbstractPrivateMessageReportTrigger extends AbstractTableTrigger
{
    public function getTable(): string
    {
        return 'private_message_report';
    }

    protected function getFields(): array
    {
        return [
            'id',
            'creator_id',
            'private_message_id',
            'original_pm_text',
            'reason',
            'resolved',
            'resolved_id',
        ];
    }
}
