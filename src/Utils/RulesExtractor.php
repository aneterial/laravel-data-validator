<?php

declare(strict_types=1);

namespace DataValidator\Utils;

use DataValidator\Attributes\Interfaces\RequestPropertyInterface;
use Illuminate\Validation\Rule;
use ReflectionClass;
use ReflectionNamedType;

final readonly class RulesExtractor
{
    /**
     * @param class-string $from
     * @param value-of<RequestPropertyInterface::ALL_TYPES> $forceType
     * @param bool $isList
     *
     * @return array<string, array<string, mixed>>
     */
    public function extractRules(string $from, ?string $forceType = null, bool $isList = false): array
    {
        $rules = [];

        foreach ((new ReflectionClass($from))->getProperties() as $property) {
            foreach ($property->getAttributes() as $attribute) {
                $attr = $attribute->newInstance();

                if (!$attr instanceof RequestPropertyInterface) {
                    continue;
                }

                $dataType = $forceType ?? $attr->getRequestDataType();
                $propertyType = $property->getType() instanceof ReflectionNamedType ? $property->getType()->getName() : '';
                $propertyName = ($isList ? '*.' : '') . $attr->getProperty();

                $rules[$dataType][$propertyName] = $attr->getRules();

                if (enum_exists($propertyType, true)) {
                    $rules[$dataType][$propertyName] = array_merge(
                        explode('|', $attr->getRules()),
                        [Rule::enum($propertyType)]
                    );
                } elseif (class_exists($propertyType, true)) {
                    $nodeRules = $this->extractRules(from: $propertyType, forceType : $dataType)[$dataType] ?? [];

                    foreach ($nodeRules as $key => $rule) {
                        $rules[$dataType][$propertyName . '.' . $key] = $rule;
                    }
                } elseif ($propertyType === 'array' && !is_null($attr->getListRules())) {
                    if (enum_exists($attr->getListRules(), true)) {
                        $rules[$dataType][$propertyName . '.*'] = Rule::enum($attr->getListRules());
                    } elseif (class_exists($attr->getListRules(), true)) {
                        $nodeRules = $this->extractRules(from: $attr->getListRules(), forceType : $dataType, isList: true)[$dataType] ?? [];

                        foreach ($nodeRules as $key => $rule) {
                            $rules[$dataType][$propertyName . '.' . $key] = $rule;
                        }
                    } else {
                        $rules[$dataType][$propertyName . '.*'] = $attr->getListRules();
                    }
                }
            }
        }

        return $rules;
    }
}
