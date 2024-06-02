<?php

declare(strict_types=1);

namespace DataValidator\Attributes\Interfaces;

interface RequestPropertyInterface
{
    public const QUERY_TYPE = 'query';
    public const BODY_TYPE = 'body';

    /** @var list<literal-string> */
    public const ALL_TYPES = [
        self::QUERY_TYPE,
        self::BODY_TYPE,
    ];

    /**
     * @return literal-string
     */
    public function getProperty(): string;

    /**
     * @return literal-string
     */
    public function getRules(): string;

    /**
     * @return value-of<self::ALL_TYPES>
     */
    public function getRequestDataType(): string;

    /**
     * @return null|class-string|enum-string|literal-string
     */
    public function getListRules(): ?string;
}
