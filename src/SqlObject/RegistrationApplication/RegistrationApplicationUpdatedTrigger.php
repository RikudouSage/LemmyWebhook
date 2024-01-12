<?php

namespace App\SqlObject\RegistrationApplication;

use App\Enum\DatabaseOperation;
use Override;

final readonly class RegistrationApplicationUpdatedTrigger extends AbstractRegistrationApplicationTrigger
{
    #[Override]
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Update;
    }

    #[Override]
    public function getName(): string
    {
        return 'rikudou_registration_application_updated';
    }
}
