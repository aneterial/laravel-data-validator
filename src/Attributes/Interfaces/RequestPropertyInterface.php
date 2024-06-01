<?php

declare(strict_types=1);

namespace DataValidator\Attributes\Interfaces;

interface RequestPropertyInterface
{
    public const QUERY_TYPE = 'query';
    public const POST_TYPE = 'post';

    /** @var list<string> */
    public const ALL_TYPES = [
        self::QUERY_TYPE,
        self::POST_TYPE,
    ];

    public function getProperty(): string;

    public function getRules(): string;

    /**
     * @return value-of<self::ALL_TYPES>
     */
    public function getHttpType(): string;

    /**
     * @return null|class-string|enum-string|'string'|'integer'|'float'
     */
    public function getListType(): ?string;
}
