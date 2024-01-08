<?php

namespace App\SqlObject\Instance;

use App\Enum\DatabaseOperation;
use Override;

final readonly class InstanceCreatedTrigger extends AbstractInstanceTrigger
{
    #[Override]
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Insert;
    }

    #[Override]
    public function getName(): string
    {
        return 'rikudou_instance_created';
    }
}
