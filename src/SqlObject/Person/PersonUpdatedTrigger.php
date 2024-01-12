<?php

namespace App\SqlObject\Person;

use App\Enum\DatabaseOperation;
use Override;

final readonly class PersonUpdatedTrigger extends AbstractPersonTrigger
{
    #[Override]
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Update;
    }

    #[Override]
    public function getName(): string
    {
        return 'rikudou_person_updated';
    }
}
