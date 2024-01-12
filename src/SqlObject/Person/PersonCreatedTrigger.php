<?php

namespace App\SqlObject\Person;

use App\Enum\DatabaseOperation;
use Override;

final readonly class PersonCreatedTrigger extends AbstractPersonTrigger
{
    #[Override]
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Insert;
    }

    #[Override]
    public function getName(): string
    {
        return 'rikudou_person_created';
    }
}
