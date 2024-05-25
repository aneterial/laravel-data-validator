<?php

declare(strict_types=1);

namespace DataValidator;

use DataValidator\Attributes\Interfaces\RequestPropertyInterface;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;

final readonly class ValidationManager
{
    public function __construct(private Factory $validationFactory)
    {
    }

    /**
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
                postRules: $rules[RequestPropertyInterface::POST_TYPE] ?? []
            )
        );

        return $hydrateObject;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T[]
     *
     * @throws ValidationException
     */
    public function validateAndHydrateList(Request $request, string $class): array
    {
        $rules = $this->extractRules(new $class());
        $hydrateList = [];

        $data = (array)(empty($rules[RequestPropertyInterface::POST_TYPE]) ? $request->query() : $request->post());
        $dataRules = $rules[RequestPropertyInterface::POST_TYPE] ?? $rules[RequestPropertyInterface::QUERY_TYPE] ?? [];

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
     * @template T of object
     *
     * @param class-string<T> $class
     * @param array<int|string, mixed> $data
     * @param value-of<RequestPropertyInterface::ALL_TYPES> $type
     *
     * @return ($isList is true ? T[] : T)
     *
     * @throws ValidationException
     */
    private function validateAndHydrateChild(string $class, array $data, string $type, bool $isList = false): object|array
    {
        $rules = $this->extractRules(new $class())[$type] ?? [];
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

        return $isList ? $hydrateList : current($hydrateList);
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
     * @return array<string, array<int|string, string>>
     */
    private function extractRules(object $fromObject): array
    {
        $rules = [];

        foreach ((new ReflectionClass($fromObject))->getProperties() as $property) {
            foreach ($property->getAttributes() as $attribute) {
                $attr = $attribute->newInstance();

                if ($attr instanceof RequestPropertyInterface) {
                    $rules[$attr->getHttpType()][$attr->getProperty()] = $attr->getRules();

                    if (!is_null($attr->getListType()) && !class_exists($attr->getListType(), true)) {
                        $rules[$attr->getHttpType()][$attr->getProperty() . '.*'] = $attr->getListType();
                    }
                }
            }
        }

        return $rules;
    }

    /**
     * @param array<int|string, mixed> $withData
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
     * @param array<int|string, mixed> $withData
     */
    private function resolveValue(ReflectionProperty $property, RequestPropertyInterface $attribute, array $withData): mixed
    {
        $data = $withData[$attribute->getProperty()] ?? null;
        $typeName = $property->getType() instanceof ReflectionNamedType
            ? $property->getType()->getName()
            : '';

        return match (true) {
            is_null($data) => null,
            class_exists($typeName) => $this->validateAndHydrateChild(
                class: $typeName,
                data: $data,
                type: $attribute->getHttpType()
            ),
            $typeName === 'array' && class_exists($attribute->getListType(), true) => $this->validateAndHydrateChild(
                class: $attribute->getListType(),
                data: $data,
                type: $attribute->getHttpType(),
                isList: true
            ),
            default => $data
        };
    }
}
