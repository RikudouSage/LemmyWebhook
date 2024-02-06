<?php

namespace App\SqlObject\LocalUser;

use App\Enum\DatabaseOperation;
use Override;

final readonly class LocalUserCreatedTrigger extends AbstractLocalUserTrigger
{
    #[Override]
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Insert;
    }

    #[Override]
    public function getName(): string
    {
        return 'rikudou_local_user_created';
    }
}
