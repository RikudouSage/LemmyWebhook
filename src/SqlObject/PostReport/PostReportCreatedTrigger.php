<?php

namespace App\SqlObject\PostReport;

use App\Enum\DatabaseOperation;
use Override;

final readonly class PostReportCreatedTrigger extends AbstractPostReportTrigger
{
    #[Override]
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Insert;
    }

    #[Override]
    public function getName(): string
    {
        return 'rikudou_post_report_created';
    }
}
