<?php

namespace App\SqlObject\Instance;

use App\Enum\DatabaseOperation;
use Override;

final readonly class InstanceDeletedTrigger extends AbstractInstanceTrigger
{
    #[Override]
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Delete;
    }

    #[Override]
    public function getName(): string
    {
        return 'rikudou_instance_deleted';
    }
}
