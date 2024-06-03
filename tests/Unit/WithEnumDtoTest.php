<?php

declare(strict_types=1);

namespace Tests\Unit;

use DataValidator\DataManager;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\DTO\WithEnumDTO;
use Tests\TestCase;

final class WithEnumDtoTest extends TestCase
{
    private ?DataManager $dataManager = null;

    /**
     * @param array<string, mixed> $postData
     *
     * @return void
     */
    #[Test]
    #[DataProvider('validWithEnumDtoProvider')]
    public function validWithEnumDto(array $postData): void
    {
        $req = new Request(request: $postData);

        $dto = $this->dataManager->validateAndConvert(from: $req, to: WithEnumDTO::class);

        $this->assertInstanceOf(expected: WithEnumDTO::class, actual: $dto);
        $this->assertSame(expected: $postData['id'], actual: $dto->id);
        $this->assertSame(expected: $postData['color'], actual: $dto->color->value);
    }

    /**
     * @param array<string, mixed> $queryData
     * @param array<string, mixed> $postData
     * @param list<string> $invalidKeys
     *
     * @return void
     */
    #[Test]
    #[DataProvider('invalidWithEnumDtoProvider')]
    public function invalidWithEnumDto(array $queryData, array $postData, array $invalidKeys): void
    {
        $req = new Request(query: $queryData, request: $postData);

        try {
            $this->dataManager->validateAndConvert(from: $req, to: WithEnumDTO::class);
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
    public static function validWithEnumDtoProvider(): array
    {
        return [
            'set_1' => [
                'postData' => [
                    'id' => 10,
                    'color' => '#ffffff',
                ],

            ],
            'set_2' => [
                'postData' => [
                    'id' => 5,
                    'color' => '#000000',
                ],

            ],
        ];
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: array<string, mixed>, 2: list<string>}>
     */
    public static function invalidWithEnumDtoProvider(): array
    {
        return [
            'data_in_wrong_place' => [
                'queryData' => [
                    'id' => 10,
                ],
                'postData' => [
                    'color' => '#ffffff',
                ],
                'invalidKeys' => ['id']
            ],
            'invalid_data_values' => [
                'queryData' => [],
                'postData' => [
                    'id' => 5,
                    'color' => 'red',
                ],
                'invalidKeys' => ['color']
            ],
            'invalid_data_types' => [
                'queryData' => [],
                'postData' => [
                    'id' => 'some id',
                    'color' => 10,
                ],
                'invalidKeys' => ['id', 'color']
            ],
            'empty_data_values' => [
                'queryData' => [],
                'postData' => [
                    'id' => 10,
                ],
                'invalidKeys' => ['color']
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
