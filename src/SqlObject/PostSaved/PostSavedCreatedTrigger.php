<?php

namespace App\SqlObject\PostSaved;

use App\Enum\DatabaseOperation;

final readonly class PostSavedCreatedTrigger extends AbstractPostSavedTrigger
{
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Insert;
    }

    public function getName(): string
    {
        return 'rikudou_post_saved_created';
    }
}
