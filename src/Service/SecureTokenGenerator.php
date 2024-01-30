<?php

namespace App\Service;

interface SecureTokenGenerator
{
    public function generate(): string;
}
