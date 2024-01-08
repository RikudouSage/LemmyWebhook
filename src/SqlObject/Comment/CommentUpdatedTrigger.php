<?php

namespace App\SqlObject\Comment;

use App\Enum\DatabaseOperation;
use Override;

final readonly class CommentUpdatedTrigger extends AbstractCommentTrigger
{
    #[Override]
    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Update;
    }

    #[Override]
    public function getName(): string
    {
        return 'rikudou_comment_updated';
    }
}
