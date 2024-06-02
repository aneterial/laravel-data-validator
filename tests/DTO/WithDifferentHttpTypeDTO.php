<?php

declare(strict_types=1);

namespace Tests\DTO;

use DataValidator\Attributes\RequestProperty;

final readonly class WithDifferentHttpTypeDTO
{
    #[RequestProperty(property: 'id', rules: 'required|integer|min:0', requestDataType: RequestProperty::QUERY_TYPE)]
    public int $id;

    #[RequestProperty(property: 'email', rules: 'required|string|email', requestDataType: RequestProperty::BODY_TYPE)]
    public string $email;

    #[RequestProperty(property: 'is_active', rules: 'required|boolean', requestDataType: RequestProperty::QUERY_TYPE)]
    public bool $isActive;

    #[RequestProperty(property: 'percent', rules: 'required|decimal:2', requestDataType: RequestProperty::BODY_TYPE)]
    public float $percent;

    #[RequestProperty(property: 'birthday', rules: 'required|string|date_format:Y-m-d H:i:s', requestDataType: RequestProperty::BODY_TYPE)]
    public string $birthday;
}
