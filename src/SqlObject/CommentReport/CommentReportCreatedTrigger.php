<?php

namespace App\SqlObject\CommentReport;

use App\Enum\DatabaseOperation;
use Override;

final readonly class CommentReportCreatedTrigger extends AbstractCommentReportTrigger
{
    #[Override]
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Insert;
    }

    #[Override]
    public function getName(): string
    {
        return 'rikudou_comment_report_created';
    }
}
