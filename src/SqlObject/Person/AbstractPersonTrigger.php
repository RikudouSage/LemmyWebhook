<?php

namespace App\SqlObject\Person;

use App\SqlObject\AbstractTableTrigger;

abstract readonly class AbstractPersonTrigger extends AbstractTableTrigger
{
    public function getTable(): string
    {
        return 'person';
    }

    protected function getFields(): array
    {
        return [
            'id',
            'name',
            'display_name',
            'avatar',
            'banned',
            'actor_id',
            'bio',
            'local',
            'banner',
            'deleted',
            'matrix_user_id',
            'admin',
            'bot_account',
            'ban_expires',
            'instance_id',
        ];
    }
}
