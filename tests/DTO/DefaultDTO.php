<?php

declare(strict_types=1);

namespace Tests\DTO;

use DataValidator\Attributes\RequestProperty;

final readonly class DefaultDTO
{
    #[RequestProperty(property: 'id', rules: 'required|integer|min:0')]
    public int $id;

    #[RequestProperty(property: 'email', rules: 'required|string|email')]
    public string $email;

    #[RequestProperty(property: 'is_active', rules: 'required|boolean')]
    public bool $isActive;

    #[RequestProperty(property: 'percent', rules: 'required|decimal:2')]
    public float $percent;

    #[RequestProperty(property: 'birthday', rules: 'required|string|date_format:Y-m-d H:i:s')]
    public string $birthday;
}
