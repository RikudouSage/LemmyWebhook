<?php

namespace App\SqlObject\PostReport;

use App\SqlObject\AbstractTableTrigger;

abstract readonly class AbstractPostReportTrigger extends AbstractTableTrigger
{
    public function getTable(): string
    {
        return 'post_report';
    }

    protected function getFields(): array
    {
        return [
            'id',
            'creator_id',
            'post_id',
            'original_post_name',
            'original_post_url',
            'original_post_body',
            'reason',
            'resolved',
            'resolver_id',
        ];
    }
}
