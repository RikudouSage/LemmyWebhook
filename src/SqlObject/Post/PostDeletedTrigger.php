<?php

namespace App\SqlObject\Post;

use App\Enum\DatabaseOperation;

final readonly class PostDeletedTrigger extends AbstractPostTrigger
{
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Delete;
    }

    public function getName(): string
    {
        return 'rikudou_post_deleted';
    }
}
