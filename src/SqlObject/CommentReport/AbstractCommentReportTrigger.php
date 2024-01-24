<?php

namespace App\SqlObject\CommentReport;

use App\SqlObject\AbstractTableTrigger;

abstract readonly class AbstractCommentReportTrigger extends AbstractTableTrigger
{
    public function getTable(): string
    {
        return 'comment_report';
    }

    protected function getFields(): array
    {
        return [
            'id',
            'creator_id',
            'comment_id',
            'original_comment_text',
            'reason',
            'resolved',
            'resolver_id',
        ];
    }
}
