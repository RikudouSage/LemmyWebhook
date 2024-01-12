<?php

namespace App\SqlObject\RegistrationApplication;

use App\Enum\DatabaseOperation;
use Override;

final readonly class RegistrationApplicationDeletedTrigger extends AbstractRegistrationApplicationTrigger
{
    #[Override]
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Delete;
    }

    #[Override]
    public function getName(): string
    {
        return 'rikudou_registration_application_deleted';
    }
}
