<?php

namespace App\SqlObject\Person;

use App\Enum\DatabaseOperation;
use Override;

final readonly class PersonDeletedTrigger extends AbstractPersonTrigger
{
    #[Override]
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Delete;
    }

    #[Override]
    public function getName(): string
    {
        return 'rikudou_person_deleted';
    }
}
