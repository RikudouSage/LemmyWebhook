<?php

namespace App\Service;

use DateTimeInterface;
use LogicException;
use Override;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use function Rikudou\ArrayMergeRecursive\array_merge_recursive;

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
            new ExpressionFunction(
                'transliterate',
                fn () => throw new LogicException('This function cannot be compiled.'),
                function (array $context, string $text): string {
                    return transliterator_transliterate('NFKC; Any-Latin; Latin-ASCII;', $text);
                },
            ),
            new ExpressionFunction(
                'merge',
                fn () => throw new LogicException('This function cannot be compiled.'),
                function (array $context, array|object ...$arrays): array {
                    $arrays = array_map(fn (array|object $item) => $this->toArray($item), $arrays);
                    return array_merge_recursive(...$arrays);
                },
            ),
        ];
    }

    private function toArray(array|object $array): array
    {
        if (is_object($array)) {
            $array = (array) $array;
        }
        foreach ($array as $key => $value) {
            if ($value instanceof DateTimeInterface) {
                $array[$key] = $value->format('c');
            } elseif (is_object($value)) {
                $array[$key] = $this->toArray($value);
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }
}
