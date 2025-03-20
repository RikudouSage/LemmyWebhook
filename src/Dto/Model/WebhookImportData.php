<?php

namespace App\Dto\Model;

use App\Enum\DatabaseOperation;
use App\Enum\RequestMethod;
use App\Enum\SigningMode;

final readonly class WebhookImportData
{
    /**
     * @param array<string, string>|null $headers
     */
    public function __construct(
        public string $uniqueMachineName,
        public string $url,
        public RequestMethod $method,
        public string $objectType,
        public DatabaseOperation $operation,
        public ?string $bodyExpression = null,
        public ?string $filterExpression = null,
        public ?string $enhancedFilterExpression = null,
        public ?array $headers = null,
        public bool $enabled = true,
        public SigningMode $signingMode = SigningMode::None,
        public ?string $signingKey = null,
    ) {
    }
}
