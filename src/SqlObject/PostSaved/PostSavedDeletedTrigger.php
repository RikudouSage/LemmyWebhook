<?php

namespace App\SqlObject\PostSaved;

use App\Enum\DatabaseOperation;

final readonly class PostSavedDeletedTrigger extends AbstractPostSavedTrigger
{
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Delete;
    }

    public function getName(): string
    {
        return 'rikudou_post_saved_deleted';
    }
}
