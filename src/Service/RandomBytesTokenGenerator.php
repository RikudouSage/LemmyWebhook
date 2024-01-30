<?php

namespace App\Service;

use Override;

final readonly class RandomBytesTokenGenerator implements SecureTokenGenerator
{
    #[Override]
    public function generate(): string
    {
        return bin2hex(random_bytes(90));
    }
}
