<?php

declare(strict_types=1);

namespace Tests\DTO;

use DataValidator\Attributes\RequestProperty;

final readonly class ExtremeNestingDTO
{
    #[RequestProperty(property: 'child', rules: 'required|array')]
    public NestingDTO $child;

    /** @var list<NestingDTO> */
    #[RequestProperty(property: 'children', rules: 'required|list', listRules: NestingDTO::class)]
    public array $children;
}
