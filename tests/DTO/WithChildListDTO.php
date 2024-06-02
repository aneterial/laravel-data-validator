<?php

declare(strict_types=1);

namespace Tests\DTO;

use DataValidator\Attributes\RequestProperty;

final readonly class WithChildListDTO
{
    #[RequestProperty(property: 'uid', rules: 'required|integer|min:0')]
    public int $uid;

    /** @var list<DefaultDTO> */
    #[RequestProperty(property: 'children', rules: 'required|list', listRules: DefaultDTO::class)]
    public array $children;
}
