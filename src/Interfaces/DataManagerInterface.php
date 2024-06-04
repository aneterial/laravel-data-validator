<?php

declare(strict_types=1);

namespace DataValidator\Interfaces;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

interface DataManagerInterface
{
    /**
     * @param Request $request
     * @param array<int|string, string|string[]> $queryRules
     * @param array<int|string, string|string[]> $bodyRules
     *
     * @return array<int|string, mixed>
     *
     * @throws ValidationException
     */
    public function validate(Request $request, array $queryRules = [], array $bodyRules = []): array;

    /**
     * @template T of object
     *
     * @param Request $from
     * @param class-string<T> $to
     *
     * @return T
     *
     * @throws ValidationException
     */
    public function validateAndConvert(Request $from, string $to): object;

    /**
     * List method only supports body data type, query type of properties is ignoring
     *
     * @template T of object
     *
     * @param Request $from
     * @param class-string<T> $to
     *
     * @return list<T>
     *
     * @throws ValidationException
     */
    public function validateAndConvertList(Request $from, string $to): array;
}
