<?php

namespace App\SqlObject\LocalUser;

use App\SqlObject\AbstractTableTrigger;

abstract readonly class AbstractLocalUserTrigger extends AbstractTableTrigger
{
    public function getTable(): string
    {
        return 'local_user';
    }

    public function getFields(): array
    {
        return [
            'id',
            'person_id',
            'email',
            'email_verified',
            'accepted_application',
            'admin',
            'totp_2fa_enabled',
        ];
    }
}
