<?php

namespace App\SqlObject\PostReport;

use App\Enum\DatabaseOperation;
use Override;

final readonly class PostReportUpdatedTrigger extends AbstractPostReportTrigger
{
    #[Override]
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Update;
    }

    #[Override]
    public function getName(): string
    {
        return 'rikudou_post_report_updated';
    }
}
