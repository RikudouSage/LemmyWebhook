<?php

namespace App\Service;

use LogicException;
use Override;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

final readonly class StandardExpressionParserProvider implements ExpressionFunctionProviderInterface
{
    #[Override]
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'string_contains',
                fn () => throw new LogicException('This function cannot be compiled.'),
                function (array $context, string $haystack, string $needle): bool {
                    return str_contains($haystack, $needle);
                }
            ),
            new ExpressionFunction(
                'lowercase',
                fn () => throw new LogicException('This function cannot be compiled.'),
                function (array $context, string $text): string {
                    return mb_strtolower($text);
                },
            ),
        ];
    }
}
