<?php

namespace App\Service\Signer;

use App\Enum\SigningMode;
use LogicException;

final readonly class SymmetricWebhookSigner implements WebhookSigner
{
    private const string PREFIX = 'whsec_';

    public function sign(string $signingKey, string $messageId, string $body, int $timestamp): string
    {
        if (str_starts_with($signingKey, self::PREFIX)) {
            $signingKey = substr($signingKey, strlen(self::PREFIX));
        }
        $signingKey = base64_decode($signingKey);
        if ($signingKey === false) {
            throw new LogicException('Invalid signing key provided, cannot be base64 decoded');
        }

        $toSign = "{$messageId}.{$timestamp}.{$body}";
        $hash = hash_hmac('sha256', $toSign, $signingKey);
        $signature = base64_encode(pack("H*", $hash));

        return "v1,{$signature}";
    }

    public function supports(SigningMode $signingMode): bool
    {
        return $signingMode === SigningMode::Symmetric;
    }
}
