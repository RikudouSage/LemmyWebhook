<?php

namespace App\SqlObject\PrivateMessageReport;

use App\Enum\DatabaseOperation;
use Override;

final readonly class PrivateMessageReportUpdatedTrigger extends AbstractPrivateMessageReportTrigger
{
    #[Override]
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Update;
    }

    #[Override]
    public function getName(): string
    {
        return 'rikudou_private_message_report_updated';
    }
}
