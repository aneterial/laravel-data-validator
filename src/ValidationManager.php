<?php

declare(strict_types=1);

namespace DataValidator;

use BackedEnum;
use DataValidator\Attributes\Interfaces\RequestPropertyInterface;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use ValueError;

/**
 * @todo parse rules from all children at once
 * @todo valid error field from nested properties
 */
final readonly class ValidationManager
{
    public function __construct(private Factory $validationFactory)
    {
    }

    /**
     * @param Request $request
     * @param array<int|string, string|string[]> $queryRules
     * @param array<int|string, string|string[]> $postRules
     *
     * @return array<int|string, mixed>
     *
     * @throws ValidationException
     */
    public function validate(Request $request, array $queryRules = [], array $postRules = []): array
    {
        $queryData = empty($queryRules) ? [] : $this->validateData(data: (array)$request->query(), rules: $queryRules);
        $postData = empty($postRules) ? [] : $this->validateData(data: (array)$request->post(), rules: $postRules);

        return array_merge($queryData, $postData);
    }

    /**
     * @template T of object
     *
     * @param Request $request
     * @param class-string<T> $class
     *
     * @return T
     *
     * @throws ValidationException
     */
    public function validateAndHydrate(Request $request, string $class): object
    {
        $hydrateObject = new $class();
        $rules = $this->extractRules($hydrateObject);

        $this->hydrate(
            object: $hydrateObject,
            withData: $this->validate(
                request: $request,
                queryRules: $rules[RequestPropertyInterface::QUERY_TYPE] ?? [],
                postRules: $rules[RequestPropertyInterface::BODY_TYPE] ?? []
            )
        );

        return $hydrateObject;
    }

    /**
     * @template T of object
     *
     * @param Request $request
     * @param class-string<T> $class
     *
     * @return list<T>
     *
     * @throws ValidationException
     */
    public function validateAndHydrateList(Request $request, string $class): array
    {
        $rules = $this->extractRules(new $class());
        $hydrateList = [];

        $data = (array)(empty($rules[RequestPropertyInterface::BODY_TYPE]) ? $request->query() : $request->post());
        $dataRules = $rules[RequestPropertyInterface::BODY_TYPE] ?? $rules[RequestPropertyInterface::QUERY_TYPE] ?? [];

        foreach ($data as $item) {
            $hydrateObject = new $class();
            $this->hydrate(
                object: $hydrateObject,
                withData: $this->validateData(data: (array)$item, rules: $dataRules),
            );

            $hydrateList[] = $hydrateObject;
        }

        return $hydrateList;
    }

    /**
     * @param array<int|string, mixed> $data
     * @param array<int|string, mixed> $rules
     *
     * @return array<int|string, mixed>
     *
     * @throws ValidationException
     */
    private function validateData(array $data, array $rules): array
    {
        $attributes = array_keys($rules);
        $validator = $this->validationFactory->make(
            data: $data,
            rules: $rules,
            attributes: array_combine($attributes, $attributes)
        );

        return $validator->validate();
    }

    /**
     * @param value-of<RequestPropertyInterface::ALL_TYPES> $forceType
     *
     * @return array<string, array<int|string, string>>
     */
    private function extractRules(object $fromObject, ?string $forceType = null): array
    {
        $rules = [];

        foreach ((new ReflectionClass($fromObject))->getProperties() as $property) {
            foreach ($property->getAttributes() as $attribute) {
                $attr = $attribute->newInstance();

                if ($attr instanceof RequestPropertyInterface) {
                    $dataType = $forceType ?? $attr->getRequestDataType();
                    $rules[$dataType][$attr->getProperty()] = $attr->getRules();

                    if (
                        !is_null($attr->getListRules())
                        && !class_exists($attr->getListRules(), true)
                        && !enum_exists($attr->getListRules(), true)
                    ) {
                        $rules[$dataType][$attr->getProperty() . '.*'] = $attr->getListRules();
                    }
                }
            }
        }

        return $rules;
    }

    /**
     * @param array<int|string, mixed> $withData
     *
     * @return void
     *
     * @throws ValidationException
     */
    private function hydrate(object &$object, array $withData): void
    {
        foreach ((new ReflectionClass($object))->getProperties() as $property) {
            foreach ($property->getAttributes() as $attribute) {
                $attr = $attribute->newInstance();

                if ($attr instanceof RequestPropertyInterface) {
                    $property->setValue(
                        value: $this->resolveValue(property: $property, attribute: $attr, withData: $withData),
                        objectOrValue: $object
                    );
                }
            }
        }
    }

    /**
     * @param ReflectionProperty $property
     * @param RequestPropertyInterface $attribute
     * @param array<int|string, mixed> $withData
     *
     * @return mixed
     *
     * @throws ValidationException
     */
    private function resolveValue(ReflectionProperty $property, RequestPropertyInterface $attribute, array $withData): mixed
    {
        $data = $withData[$attribute->getProperty()] ?? null;
        $typeName = $property->getType() instanceof ReflectionNamedType
            ? $property->getType()->getName()
            : '';

        return match (true) {
            is_null($data) => $typeName === 'array' ? [] : null,
            enum_exists($typeName, true) => $this->validateAndHydrateEnum(
                enum: $typeName,
                data: $data,
                property: $attribute->getProperty()
            ),
            class_exists($typeName, true) => $this->validateAndHydrateChild(
                class: $typeName,
                data: $data,
                type: $attribute->getRequestDataType()
            ),
            $typeName === 'array'
            && !is_null($attribute->getListRules())
            && enum_exists($attribute->getListRules(), true) => $this->validateAndHydrateEnum(
                enum: $attribute->getListRules(),
                data: $data,
                property: $attribute->getProperty(),
                isList: true
            ),
            $typeName === 'array'
            && !is_null($attribute->getListRules())
            && class_exists($attribute->getListRules(), true) => $this->validateAndHydrateChild(
                class: $attribute->getListRules(),
                data: $data,
                type: $attribute->getRequestDataType(),
                isList: true
            ),
            default => $data
        };
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     * @param array<int|string, mixed> $data
     * @param value-of<RequestPropertyInterface::ALL_TYPES> $type
     * @param bool $isList
     *
     * @return ($isList is true ? list<T> : T)
     *
     * @throws ValidationException
     */
    private function validateAndHydrateChild(string $class, array $data, string $type, bool $isList = false): object|array
    {
        $rules = $this->extractRules(fromObject: new $class(), forceType: $type)[$type] ?? [];
        $hydrateList = [];

        if (!$isList) {
            $data = [$data];
        }

        foreach ($data as $item) {
            $hydrateObject = new $class();
            $this->hydrate(
                object: $hydrateObject,
                withData: $this->validateData(data: $item, rules: $rules),
            );

            $hydrateList[] = $hydrateObject;
        }

        return $isList ? $hydrateList : reset($hydrateList);
    }

    /**
     * @param enum-string $enum
     * @param mixed $data
     * @param string $property
     *
     * @return ($isList is true ? list<BackedEnum> : BackedEnum)
     *
     * @throws ValidationException
     */
    private function validateAndHydrateEnum(string $enum, mixed $data, string $property, bool $isList = false): BackedEnum|array
    {
        if (!$isList) {
            $data = [$data];
        }

        $result = [];

        try {
            foreach ($data as $item) {
                $result[] = $enum::from($item);
            }
        } catch (ValueError) {
            throw ValidationException::withMessages([$property => sprintf(
                'The %s field must be a valid enum (%s)',
                $property,
                implode(',', array_map(static fn ($en): int|string => $en->value, $enum::cases()))
            )]);
        }

        return $isList ? $result : reset($result);
    }
}
