<?php

declare(strict_types=1);

namespace Tests\DTO;

use DataValidator\Attributes\RequestProperty;

final readonly class WithChildDTO
{
    #[RequestProperty(property: 'uid', rules: 'required|integer|min:0')]
    public int $uid;

    #[RequestProperty(property: 'child', rules: 'required|array')]
    public DefaultDTO $child;
}
