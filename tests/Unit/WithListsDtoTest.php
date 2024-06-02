<?php

declare(strict_types=1);

namespace Tests\Unit;

use DataValidator\DataManager;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\DTO\WithListsDTO;
use Tests\TestCase;

final class WithListsDtoTest extends TestCase
{
    private ?DataManager $dataManager = null;

    /**
     * @param array<string, mixed> $postData
     *
     * @return void
     */
    #[Test]
    #[DataProvider('validWithListsDtoProvider')]
    public function validWithListsDto(array $postData): void
    {
        $req = new Request(request: $postData);

        $dto = $this->dataManager->validateAndConvert(from: $req, to: WithListsDTO::class);

        $this->assertInstanceOf(expected: WithListsDTO::class, actual: $dto);
        $this->assertSame(expected: $postData['cities'], actual: $dto->cities);
        $this->assertSame(expected: $postData['codes'], actual: $dto->codes);
        $this->assertSame(expected: $postData['emails'], actual: $dto->emails);
        $this->assertSame(expected: $postData['ids'], actual: $dto->ids);
        $this->assertSame(expected: $postData['dates'], actual: $dto->dates);
    }

    /**
     * @param array<string, mixed> $queryData
     * @param array<string, mixed> $postData
     * @param list<string> $invalidKeys
     *
     * @return void
     */
    #[Test]
    #[DataProvider('invalidWithListsDtoProvider')]
    public function invalidWithListsDto(array $queryData, array $postData, array $invalidKeys): void
    {
        $req = new Request(query: $queryData, request: $postData);

        try {
            $this->dataManager->validateAndConvert(from: $req, to: WithListsDTO::class);
        } catch (ValidationException $e) {
            $this->assertSame(expected: $invalidKeys, actual: array_keys($e->errors()));
        } finally {
            $this->assertNotEmpty($e ?? null);
            $this->assertInstanceOf(ValidationException::class, $e);
        }
    }

    /**
     * @return array<string, array{0: array<string, mixed>}>
     */
    public static function validWithListsDtoProvider(): array
    {
        return [
            'set_1' => [
                'postData' => [
                    'cities' => ['Moscow'],
                    'codes' => [-2345],
                    'emails' => ['test@mail.com'],
                    'ids' => [10],
                    'dates' => ['2000-01-01 12:00:00'],
                ],

            ],
            'set_2' => [
                'postData' => [
                    'cities' => ['Moscow', 'St Petersburg'],
                    'codes' => [-23, 45, 1234],
                    'emails' => ['test@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                    'ids' => [10, 11, 12, 13],
                    'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: array<string, mixed>, 2: list<string>}>
     */
    public static function invalidWithListsDtoProvider(): array
    {
        return [
            'data_in_wrong_place' => [
                'queryData' => [
                    'cities' => ['Moscow'],
                    'codes' => [-2345],
                ],
                'postData' => [

                    'emails' => ['test@mail.com'],
                    'ids' => [10],
                    'dates' => ['2000-01-01 12:00:00'],
                ],
                'invalidKeys' => ['cities', 'codes']
            ],
            'invalid_data_values' => [
                'queryData' => [],
                'postData' => [
                    'cities' => ['Moscow', 'St Petersburg'],
                    'codes' => [-23, 45, 1234],
                    'emails' => ['test@mail.com', 'hello', 'test_1@mail.com'],
                    'ids' => [10, 11, -12, -13],
                    'dates' => ['2000232323-021-01 12:00:00', '2024-04-23 12:30:00'],
                ],
                'invalidKeys' => ['emails.1', 'ids.2', 'ids.3', 'dates.0']
            ],
            'invalid_data_types' => [
                'queryData' => [],
                'postData' => [
                    'cities' => ['Moscow', 12345.65],
                    'codes' => ['hello', 45, 1234],
                    'emails' => ['test@mail.com', 'test@mail.ru', true],
                    'ids' => ['hello', 11, 12, 13],
                    'dates' => ['2000-01-01 12:00:00', [1,2,3,4]],
                ],
                'invalidKeys' => ['cities.1', 'codes.0', 'emails.2', 'ids.0', 'dates.1']
            ],
            'empty_data_values' => [
                'queryData' => [],
                'postData' => [
                    'cities' => ['Moscow', 'St Petersburg'],
                    'ids' => [10, 11, 12, 13],
                    'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                ],
                'invalidKeys' => ['codes', 'emails']
            ],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->dataManager = $this->app->make(DataManager::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->dataManager = null;
    }
}
