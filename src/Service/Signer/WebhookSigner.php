<?php

namespace App\Service\Signer;

use App\Enum\SigningMode;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(name: 'app.webhook_signer')]
interface WebhookSigner
{
    public function sign(string $signingKey, string $messageId, string $body, int $timestamp): string;
    public function supports(SigningMode $signingMode): bool;
}
