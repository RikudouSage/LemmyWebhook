<?php

namespace App\SqlObject\RegistrationApplication;

use App\SqlObject\AbstractTableTrigger;

abstract readonly class AbstractRegistrationApplicationTrigger extends AbstractTableTrigger
{
    public function getTable(): string
    {
        return 'registration_application';
    }

    protected function getFields(): array
    {
        return [
            'id',
            'local_user_id',
            'answer',
            'admin_id',
            'deny_reason',
        ];
    }
}
