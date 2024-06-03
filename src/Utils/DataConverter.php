<?php

declare(strict_types=1);

namespace DataValidator\Utils;

use BackedEnum;
use DataValidator\Attributes\Interfaces\RequestPropertyInterface;
use Illuminate\Validation\ValidationException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use ValueError;

final readonly class DataConverter
{
    /**
     * @template T of object
     *
     * @param class-string<T> $to
     * @param array<int|string, mixed> $from
     * @param bool $isList
     *
     * @return ($isList is true ? list<T> : T)
     *
     * @throws ValidationException
     */
    public function convert(string $to, array $from, bool $isList = false): object|array
    {
        $convertedList = [];

        if (!$isList) {
            $from = [$from];
        }

        foreach ($from as $data) {
            $convertedObject = new $to();

            foreach ((new ReflectionClass($convertedObject))->getProperties() as $property) {
                foreach ($property->getAttributes() as $attribute) {
                    $attr = $attribute->newInstance();

                    if ($attr instanceof RequestPropertyInterface) {
                        $property->setValue($convertedObject, $this->resolveValue(
                            property: $property,
                            attribute: $attr,
                            from: (array) $data
                        ));
                    }
                }
            }

            $convertedList[] = $convertedObject;
        }

        return $isList ? $convertedList : $convertedList[0];
    }

    /**
     * @param ReflectionProperty $property
     * @param RequestPropertyInterface $attribute
     * @param array<int|string, mixed> $from
     *
     * @return mixed
     *
     * @throws ValidationException
     */
    private function resolveValue(ReflectionProperty $property, RequestPropertyInterface $attribute, array $from): mixed
    {
        $data = $from[$attribute->getProperty()] ?? null;
        $typeName = $property->getType() instanceof ReflectionNamedType ? $property->getType()->getName() : '';

        return match (true) {
            is_null($data) => $typeName === 'array' ? [] : null,
            enum_exists($typeName, true) => $this->convertEnum(
                to: $typeName,
                from: $data,
                property: $attribute->getProperty()
            ),
            class_exists($typeName, true) => $this->convert(
                to: $typeName,
                from: $data,
            ),
            $typeName === 'array'
            && !is_null($attribute->getListRules())
            && enum_exists($attribute->getListRules(), true) => $this->convertEnum(
                to: $attribute->getListRules(),
                from: $data,
                property: $attribute->getProperty(),
                isList: true
            ),
            $typeName === 'array'
            && !is_null($attribute->getListRules())
            && class_exists($attribute->getListRules(), true) => $this->convert(
                to: $attribute->getListRules(),
                from: $data,
                isList: true
            ),
            default => $data
        };
    }

    /**
     * @param enum-string $to
     * @param ($isList is true ? mixed[] : mixed) $from
     * @param string $property
     * @param bool $isList
     *
     * @return ($isList is true ? list<BackedEnum> : BackedEnum)
     *
     * @throws ValidationException
     */
    private function convertEnum(string $to, mixed $from, string $property, bool $isList = false): BackedEnum|array
    {
        if (!$isList) {
            $from = [$from];
        }

        $enums = [];

        try {
            foreach ($from as $item) {
                $enums[] = $to::from($item);
            }
        } catch (ValueError) {
            throw ValidationException::withMessages([$property => sprintf(
                'The %s field must be a valid enum (%s)',
                $property,
                implode(',', array_map(static fn ($en): int|string => $en->value, $to::cases()))
            )]);
        }

        return $isList ? $enums : reset($enums);
    }
}
