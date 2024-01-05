<?php

namespace App\SqlObject\Post;

use App\Enum\DatabaseOperation;
use App\SqlObject\AbstractTableTrigger;
use Doctrine\DBAL\Connection;

final readonly class PostUpdatedTrigger extends AbstractPostTrigger
{
    public function getName(): string
    {
        return 'rikudou_post_updated';
    }

    protected function getTriggerOperation(): DatabaseOperation
    {
        return DatabaseOperation::Update;
    }
}
