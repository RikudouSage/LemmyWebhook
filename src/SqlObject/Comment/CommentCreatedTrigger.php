<?php

namespace App\SqlObject\Comment;

use App\Enum\DatabaseOperation;

final readonly class CommentCreatedTrigger extends AbstractCommentTrigger
{
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Insert;
    }

    public function getName(): string
    {
        return 'rikudou_comment_created';
    }
}
