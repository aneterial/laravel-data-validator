<?php

declare(strict_types=1);

namespace DataValidator;

use DataValidator\Attributes\Interfaces\RequestPropertyInterface;
use DataValidator\Utils\DataConverter;
use DataValidator\Utils\RulesExtractor;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final readonly class DataManager
{
    public function __construct(
        private Factory        $validationFactory,
        private RulesExtractor $rulesExtractor,
        private DataConverter   $dataConverter
    ) {
    }

    /**
     * @param Request $request
     * @param array<int|string, string|string[]> $queryRules
     * @param array<int|string, string|string[]> $bodyRules
     *
     * @return array<int|string, mixed>
     *
     * @throws ValidationException
     */
    public function validate(Request $request, array $queryRules = [], array $bodyRules = []): array
    {
        $queryData = empty($queryRules) ? [] : $this->validateData(data: (array)$request->query(), rules: $queryRules);
        $postData = empty($bodyRules) ? [] : $this->validateData(data: (array)$request->post(), rules: $bodyRules);

        return array_merge($queryData, $postData);
    }

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
    public function validateAndConvert(Request $from, string $to): object
    {
        $rules = $this->rulesExtractor->extractRules($to);

        return $this->dataConverter->convert(
            to: $to,
            from: $this->validate(
                request: $from,
                queryRules: $rules[RequestPropertyInterface::QUERY_TYPE] ?? [],
                bodyRules: $rules[RequestPropertyInterface::BODY_TYPE] ?? []
            )
        );
    }

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
    public function validateAndConvertList(Request $from, string $to): array
    {
        $rules = $this->rulesExtractor->extractRules(
            from: $to,
            forceType: RequestPropertyInterface::BODY_TYPE,
            isList: true
        );

        return $this->dataConverter->convert(
            to: $to,
            from: $this->validate(request: $from, bodyRules: $rules[RequestPropertyInterface::BODY_TYPE] ?? []),
            isList: true
        );
    }

    /**
     * @param array<int|string, mixed> $data
     * @param array<int|string, mixed> $rules
     *
     * @return array<int|string, mixed>
     *
     * @throws ValidationException
     */
    private function validateData(array $data, array $rules): array
    {
        $attributes = array_keys($rules);
        $validator = $this->validationFactory->make(
            data: $data,
            rules: $rules,
            attributes: array_combine($attributes, $attributes)
        );

        return $validator->validate();
    }
}
