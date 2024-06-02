<?php

declare(strict_types=1);

namespace Tests\DTO;

use DataValidator\Attributes\RequestProperty;
use Tests\Data\ColorEnum;

final readonly class WithEnumDTO
{
    #[RequestProperty(property: 'id', rules: 'required|integer|min:0')]
    public int $id;

    #[RequestProperty(property: 'color', rules: 'required|string')]
    public ColorEnum $color;
}
