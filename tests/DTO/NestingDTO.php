<?php

declare(strict_types=1);

namespace Tests\DTO;

use DataValidator\Attributes\RequestProperty;
use Tests\Data\ColorEnum;

final readonly class NestingDTO
{
    #[RequestProperty(property: 'id', rules: 'required|integer|min:0')]
    public int $id;

    /** @var list<int> */
    #[RequestProperty(property: 'ids', rules: 'required|list', listRules: 'int|min:0')]
    public array $ids;

    #[RequestProperty(property: 'child', rules: 'required|array')]
    public WithChildListDTO $child;

    /** @var list<WithChildListDTO> */
    #[RequestProperty(property: 'children', rules: 'required|list', listRules: WithChildListDTO::class)]
    public array $children;

    /** @var list<WithListsDTO> */
    #[RequestProperty(property: 'lists', rules: 'required|list', listRules: WithListsDTO::class)]
    public array $lists;

    #[RequestProperty(property: 'color', rules: 'required|string')]
    public ColorEnum $color;

    /** @var list<ColorEnum> */
    #[RequestProperty(property: 'colors', rules: 'required|list', listRules: ColorEnum::class)]
    public array $colors;
}
