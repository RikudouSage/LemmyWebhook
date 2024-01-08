<?php

namespace App\SqlObject\Comment;

use App\Enum\DatabaseOperation;
use Override;

final readonly class CommentDeletedTrigger extends AbstractCommentTrigger
{
    #[Override]
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Delete;
    }

    #[Override]
    public function getName(): string
    {
        return 'rikudou_comment_deleted';
    }
}
