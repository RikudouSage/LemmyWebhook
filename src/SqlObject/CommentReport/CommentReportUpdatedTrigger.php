<?php

namespace App\SqlObject\CommentReport;

use App\Enum\DatabaseOperation;
use Override;

final readonly class CommentReportUpdatedTrigger extends AbstractCommentReportTrigger
{
    #[Override]
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Update;
    }

    #[Override]
    public function getName(): string
    {
        return 'rikudou_comment_report_updated';
    }
}
