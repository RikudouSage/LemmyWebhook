<?php

namespace App\SqlObject\Post;

use App\Enum\DatabaseOperation;
use App\SqlObject\AbstractTableTrigger;
use Doctrine\DBAL\Connection;

final readonly class PostCreatedTrigger extends AbstractPostTrigger
{
    public function getName(): string
    {
        return 'rikudou_post_created';
    }

    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Insert;
    }
}
