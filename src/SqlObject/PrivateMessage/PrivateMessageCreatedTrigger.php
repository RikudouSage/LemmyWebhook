<?php

namespace App\SqlObject\PrivateMessage;

use App\Enum\DatabaseOperation;
use Override;

final readonly class PrivateMessageCreatedTrigger extends AbstractPrivateMessageTrigger
{
    #[Override]
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Insert;
    }

    #[Override]
    public function getName(): string
    {
        return 'rikudou_private_message_created';
    }
}
