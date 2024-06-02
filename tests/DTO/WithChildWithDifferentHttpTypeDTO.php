<?php

declare(strict_types=1);

namespace Tests\DTO;

use DataValidator\Attributes\RequestProperty;

final readonly class WithChildWithDifferentHttpTypeDTO
{
    #[RequestProperty(property: 'uid', rules: 'required|integer|min:0', requestDataType: RequestProperty::QUERY_TYPE)]
    public int $uid;

    #[RequestProperty(property: 'child', rules: 'required|array', requestDataType: RequestProperty::BODY_TYPE)]
    public WithDifferentHttpTypeDTO $child;
}
