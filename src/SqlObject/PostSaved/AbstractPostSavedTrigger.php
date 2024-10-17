<?php

namespace App\SqlObject\PostSaved;

use App\SqlObject\AbstractTableTrigger;

abstract readonly class AbstractPostSavedTrigger extends AbstractTableTrigger
{
    public function getTable(): string
    {
        return 'post_saved';
    }

    protected function getFields(): array
    {
        return ['post_id', 'person_id', 'published'];
    }
}
