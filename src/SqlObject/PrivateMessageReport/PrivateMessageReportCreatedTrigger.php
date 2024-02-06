<?php

namespace App\SqlObject\PrivateMessageReport;

use App\Enum\DatabaseOperation;
use Override;

final readonly class PrivateMessageReportCreatedTrigger extends AbstractPrivateMessageReportTrigger
{
    #[Override]
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Insert;
    }

    #[Override]
    public function getName(): string
    {
        return 'rikudou_private_message_report_created';
    }
}
