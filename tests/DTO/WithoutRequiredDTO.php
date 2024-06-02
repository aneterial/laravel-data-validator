<?php

declare(strict_types=1);

namespace Tests\DTO;

use DataValidator\Attributes\RequestProperty;

final readonly class WithoutRequiredDTO
{
    #[RequestProperty(property: 'id', rules: 'integer|min:0')]
    public ?int $id;

    #[RequestProperty(property: 'email', rules: 'string|email')]
    public ?string $email;

    #[RequestProperty(property: 'is_active', rules: 'boolean')]
    public ?bool $isActive;

    #[RequestProperty(property: 'percent', rules: 'decimal:2')]
    public ?float $percent;

    #[RequestProperty(property: 'birthday', rules: 'string|date_format:Y-m-d H:i:s')]
    public ?string $birthday;

    /** @var list<int> */
    #[RequestProperty(property: 'codes', rules: 'list', listRules: 'integer')]
    public array $codes;
}
