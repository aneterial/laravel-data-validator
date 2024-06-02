<?php

declare(strict_types=1);

namespace DataValidator\Attributes;

use Attribute;
use DataValidator\Attributes\Interfaces\RequestPropertyInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class RequestProperty implements RequestPropertyInterface
{
    /**
     * @param literal-string                                $property
     * @param literal-string                                $rules
     * @param value-of<RequestPropertyInterface::ALL_TYPES> $requestDataType
     * @param null|class-string|enum-string|literal-string  $listRules
     */
    public function __construct(
        private string $property,
        private string $rules,
        private string $requestDataType = self::BODY_TYPE,
        private ?string $listRules = null,
    ) {
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getRules(): string
    {
        return $this->rules;
    }

    public function getRequestDataType(): string
    {
        return $this->requestDataType;
    }

    public function getListRules(): ?string
    {
        return $this->listRules;
    }
}
