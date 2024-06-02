<?php

declare(strict_types=1);

namespace Tests\DTO;

use DataValidator\Attributes\RequestProperty;

final readonly class WithListsDTO
{
    /** @var list<string> */
    #[RequestProperty(property: 'cities', rules: 'required|list', listRules: 'string')]
    public array $cities;

    /** @var list<int> */
    #[RequestProperty(property: 'codes', rules: 'required|list', listRules: 'integer')]
    public array $codes;

    /** @var list<string> */
    #[RequestProperty(property: 'emails', rules: 'required|list', listRules: 'string|email')]
    public array $emails;

    /** @var list<int> */
    #[RequestProperty(property: 'ids', rules: 'required|list', listRules: 'int|min:0')]
    public array $ids;

    /** @var list<string> */
    #[RequestProperty(property: 'dates', rules: 'required|list', listRules: 'string|date_format:Y-m-d H:i:s')]
    public array $dates;

}
