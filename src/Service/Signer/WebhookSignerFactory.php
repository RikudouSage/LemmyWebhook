<?php

namespace App\Service\Signer;

use App\Enum\SigningMode;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class WebhookSignerFactory
{
    /**
     * @param iterable<WebhookSigner> $signers
     */
    public function __construct(
        #[AutowireIterator('app.webhook_signer')]
        private iterable $signers,
    ) {
    }

    public function findSigner(SigningMode $signingMode): ?WebhookSigner
    {
        foreach ($this->signers as $signer) {
            if ($signer->supports($signingMode)) {
                return $signer;
            }
        }

        return null;
    }
}
