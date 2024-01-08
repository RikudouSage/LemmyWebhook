<?php

namespace App\SqlObject\Instance;

use App\Enum\DatabaseOperation;
use Override;

final readonly class InstanceUpdatedTrigger extends AbstractInstanceTrigger
{
    #[Override]
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Update;
    }

    #[Override]
    public function getName(): string
    {
        return 'rikudou_instance_updated';
    }
}
