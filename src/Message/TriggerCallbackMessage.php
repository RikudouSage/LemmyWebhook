<?php

namespace App\Message;

use App\Dto\RawData\RawData;
use App\Entity\Webhook;

final readonly class TriggerCallbackMessage
{
    public function __construct(
        public Webhook $webhook,
        public RawData $data,
    ) {
    }
}
