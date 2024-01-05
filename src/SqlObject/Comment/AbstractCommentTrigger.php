<?php

namespace App\SqlObject\Comment;

use App\SqlObject\AbstractTableTrigger;

abstract readonly class AbstractCommentTrigger extends AbstractTableTrigger
{
    public function getTable(): string
    {
        return 'comment';
    }

    protected function getFields(): array
    {
        return [
            'id',
            'creator_id',
            'post_id',
            'content',
            'removed',
            'deleted',
            'ap_id',
            'local',
            'distinguished',
        ];
    }
}
