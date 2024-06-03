<?php

declare(strict_types=1);

namespace Tests\Unit;

use DataValidator\DataManager;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Data\ColorEnum;
use Tests\DTO\WithEnumsListDTO;
use Tests\TestCase;

final class WithEnumsListDtoTest extends TestCase
{
    private ?DataManager $dataManager = null;

    /**
     * @param array<string, mixed> $postData
     *
     * @return void
     */
    #[Test]
    #[DataProvider('validWithEnumsListDtoProvider')]
    public function validWithEnumsListDto(array $postData): void
    {
        $req = new Request(request: $postData);

        $dto = $this->dataManager->validateAndConvert(from: $req, to: WithEnumsListDTO::class);

        $this->assertInstanceOf(expected: WithEnumsListDTO::class, actual: $dto);
        $this->assertSame(expected: $postData['id'], actual: $dto->id);

        foreach ($dto->colors as $color) {
            $this->assertInstanceOf(ColorEnum::class, $color);
        }

        $this->assertSame(expected: $postData['colors'], actual: array_map(static fn (ColorEnum $c): string => $c->value, $dto->colors));
    }

    /**
     * @param array<string, mixed> $queryData
     * @param array<string, mixed> $postData
     * @param list<string> $invalidKeys
     *
     * @return void
     */
    #[Test]
    #[DataProvider('invalidWithEnumsListDtoProvider')]
    public function invalidWithEnumsListDto(array $queryData, array $postData, array $invalidKeys): void
    {
        $req = new Request(query: $queryData, request: $postData);

        try {
            $this->dataManager->validateAndConvert(from: $req, to: WithEnumsListDTO::class);
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
    public static function validWithEnumsListDtoProvider(): array
    {
        return [
            'set_1' => [
                'postData' => [
                    'id' => 10,
                    'colors' => ['#ffffff'],
                ],

            ],
            'set_2' => [
                'postData' => [
                    'id' => 5,
                    'colors' => ['#ffffff', '#000000'],
                ],

            ],
        ];
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: array<string, mixed>, 2: list<string>}>
     */
    public static function invalidWithEnumsListDtoProvider(): array
    {
        return [
            'data_in_wrong_place' => [
                'queryData' => [
                    'id' => 10,
                ],
                'postData' => [
                    'colors' => ['#ffffff'],
                ],
                'invalidKeys' => ['id']
            ],
            'invalid_data_values' => [
                'queryData' => [],
                'postData' => [
                    'id' => 5,
                    'colors' => ['#ffffff', 'red'],
                ],
                'invalidKeys' => ['colors.1']
            ],
            'invalid_data_types' => [
                'queryData' => [],
                'postData' => [
                    'id' => 1,
                    'colors' => '#ffffff',
                ],
                'invalidKeys' => ['colors']
            ],
            'empty_data_values' => [
                'queryData' => [],
                'postData' => [
                    'id' => 10,
                ],
                'invalidKeys' => ['colors']
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
