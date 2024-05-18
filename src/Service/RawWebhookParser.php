<?php

namespace App\Service;

use App\Attribute\RawDataType;
use App\Dto\RawData\RawData;
use BackedEnum;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use LogicException;
use ReflectionClass;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionObject;
use ReflectionProperty;
use ReflectionUnionType;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final readonly class RawWebhookParser
{
    /**
     * @param iterable<object> $types
     */
    public function __construct(
        #[TaggedIterator('app.raw_data_type')]
        private iterable $types,
    ) {
    }

    /**
     * @return RawData<object>
     */
    public function parse(array $raw): RawData
    {
        $data = $raw['data'] ?? [];
        $previous = $raw['previous'] ?? null;
        unset($raw['data'], $raw['previous']);


        $rawObject = $this->deserialize($raw, RawData::class);

        $objectTypeClass = $this->findTypeObjectClass($rawObject->table);

        $dataObject = $this->deserialize($data, $objectTypeClass);
        $previousObject = $previous === null ? null : $this->deserialize($previous, $objectTypeClass);

        $dataProperty = new ReflectionProperty(RawData::class, 'data');
        $dataProperty->setValue($rawObject, $dataObject);
        $previousProperty = new ReflectionProperty(RawData::class, 'previous');
        $previousProperty->setValue($rawObject, $previousObject);

        return $rawObject;
    }

    /**
     * @template TObject
     * @param class-string<TObject> $class
     * @return TObject
     */
    public function deserialize(array $raw, string $class): object
    {
        $reflection = new ReflectionClass($class);
        $object = $reflection->newInstance();

        foreach ($raw as $key => $value) {
            $propertyName = preg_replace_callback('@_([a-z0-9])@', function (array $matches) {
                return strtoupper($matches[1]);
            }, $key);
            if (str_ends_with($propertyName, '_')) {
                $propertyName = substr($propertyName, 0, -1);
            }
            try {
                $propertyReflection = $reflection->getProperty($propertyName);
            } catch (ReflectionException $e) {
                if (str_ends_with($e->getMessage(), 'does not exist')) {
                    continue;
                }
                throw $e;
            }
            $targetValue = $this->parseValue($value, $propertyReflection->getType());
            $propertyReflection->setValue($object, $targetValue);
        }

        return $object;
    }

    public function isValidTable(string $table): bool
    {
        try {
            $this->findTypeObjectClass($table);
            return true;
        } catch (LogicException) {
            return false;
        }
    }

    /**
     * @return class-string<object>
     */
    private function findTypeObjectClass(string $table): string
    {
        foreach ($this->types as $type) {
            $reflection = new ReflectionObject($type);
            $attribute = $reflection->getAttributes(RawDataType::class)[0]->newInstance();
            assert($attribute instanceof RawDataType);
            if ($attribute->table === $table) {
                return $reflection->getName();
            }
        }

        throw new LogicException("Unknown object type: {$table}");
    }

    private function parseValue(mixed $value, ReflectionIntersectionType|ReflectionNamedType|ReflectionUnionType|null $type): mixed
    {
        if ($type === null) {
            return $value;
        }

        if (!$type instanceof ReflectionNamedType) {
            throw new LogicException('Only single (or nullable) named types are supported.');
        }

        if ($type->allowsNull() && $value === null) {
            return null;
        }

        $typeName = $type->getName();

        if (is_a($typeName, DateTime::class, true)) {
            return new DateTime($value);
        } elseif (is_a($typeName, DateTimeInterface::class, true)) {
            return new DateTimeImmutable($value);
        } elseif (is_a($typeName, BackedEnum::class, true)) {
            return $typeName::from($value);
        } elseif ($typeName === 'bool' && ($value === 'true' || $value === 'false')) {
            return $value === 'true';
        } else {
            return $value;
        }
    }
}
