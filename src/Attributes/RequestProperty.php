<?php

declare(strict_types=1);

namespace DataValidator\Attributes;

use Attribute;
use DataValidator\Attributes\Interfaces\RequestPropertyInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class RequestProperty implements RequestPropertyInterface
{
    /**
     * @param value-of<RequestPropertyInterface::ALL_TYPES> $httpType
     * @param null|class-string|'string'|'integer'|'float'  $listType
     */
    public function __construct(
        private string $property,
        private string $rules,
        private string $httpType = self::POST_TYPE,
        private ?string $listType = null,
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

    public function getHttpType(): string
    {
        return $this->httpType;
    }

    public function getListType(): ?string
    {
        return $this->listType;
    }
}
