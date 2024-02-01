<?php

namespace App\Listener;


use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(Events::preFlush)]
final readonly class ForeignKeysEnabled
{
    public function preFlush(PreFlushEventArgs $event): void
    {
        $event->getObjectManager()->getConnection()->executeStatement('PRAGMA foreign_keys = ON');
    }
}
