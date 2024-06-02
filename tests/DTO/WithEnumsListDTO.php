<?php

declare(strict_types=1);

namespace Tests\DTO;

use DataValidator\Attributes\RequestProperty;
use Tests\Data\ColorEnum;

final readonly class WithEnumsListDTO
{
    #[RequestProperty(property: 'id', rules: 'required|integer|min:0')]
    public int $id;

    /** @var list<ColorEnum> */
    #[RequestProperty(property: 'colors', rules: 'required|list', listRules: ColorEnum::class)]
    public array $colors;
}
