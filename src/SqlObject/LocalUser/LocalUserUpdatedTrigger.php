<?php

namespace App\SqlObject\LocalUser;

use App\Enum\DatabaseOperation;
use Override;

final readonly class LocalUserUpdatedTrigger extends AbstractLocalUserTrigger
{
    #[Override]
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Update;
    }

    #[Override]
    public function getName(): string
    {
        return 'rikudou_local_user_updated';
    }
}
