<?php

declare(strict_types=1);

namespace Tests\Unit;

use DataValidator\DataManager;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\DTO\WithDifferentHttpTypeDTO;
use Tests\TestCase;

final class WithDifferentHttpTypeDtoTest extends TestCase
{
    private ?DataManager $dataManager = null;

    /**
     * @param array<string, mixed> $queryData
     * @param array<string, mixed> $postData
     *
     * @return void
     */
    #[Test]
    #[DataProvider('validWithDifferentHttpTypeDtoProvider')]
    public function validWithDifferentHttpTypeDto(array $queryData, array $postData): void
    {
        $req = new Request(query: $queryData, request: $postData);

        $dto = $this->dataManager->validateAndConvert(from: $req, to: WithDifferentHttpTypeDTO::class);

        $this->assertInstanceOf(expected: WithDifferentHttpTypeDTO::class, actual: $dto);
        $this->assertSame(expected: $queryData['id'], actual: $dto->id);
        $this->assertSame(expected: $postData['email'], actual: $dto->email);
        $this->assertSame(expected: $postData['birthday'], actual: $dto->birthday);
        $this->assertTrue($queryData['is_active'] == $dto->isActive);
        $this->assertTrue($postData['percent'] == $dto->percent);
    }

    /**
     * @param array<string, mixed> $queryData
     * @param array<string, mixed> $postData
     * @param list<string> $invalidKeys
     *
     * @return void
     */
    #[Test]
    #[DataProvider('invalidWithDifferentHttpTypeDtoProvider')]
    public function invalidWithDifferentHttpTypeDto(array $queryData, array $postData, array $invalidKeys): void
    {
        $req = new Request(query: $queryData, request: $postData);

        try {
            $this->dataManager->validateAndConvert(from: $req, to: WithDifferentHttpTypeDTO::class);
        } catch (ValidationException $e) {
            $this->assertSame(expected: $invalidKeys, actual: array_keys($e->errors()));
        } finally {
            $this->assertNotEmpty($e ?? null);
            $this->assertInstanceOf(ValidationException::class, $e);
        }
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: array<string, mixed>}>
     */
    public static function validWithDifferentHttpTypeDtoProvider(): array
    {
        return [
            'set_1' => [
                'queryData' => [
                    'id' => 10,
                    'is_active' => 1,
                ],
                'postData' => [
                    'email' => 'test@mail.com',
                    'percent' => '10.21',
                    'birthday' => '2000-01-01 12:00:00',
                ],

            ],
            'set_2' => [
                'queryData' => [
                    'id' => 17,
                    'is_active' => 0,
                ],
                'postData' => [
                    'email' => 'test_test@mail.com',
                    'percent' => -45.45,
                    'birthday' => '1990-12-24 06:34:00',
                ],

            ],
        ];
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: array<string, mixed>, 2: list<string>}>
     */
    public static function invalidWithDifferentHttpTypeDtoProvider(): array
    {
        return [
            'data_in_wrong_place_1' => [
                'queryData' => [
                    'id' => 10,
                    'email' => 'test@mail.com',
                ],
                'postData' => [
                    'is_active' => 1,
                    'percent' => '10.21',
                    'birthday' => '2000-01-01 12:00:00'
                ],
                'invalidKeys' => ['is_active']
            ],
            'data_in_wrong_place_2' => [
                'queryData' => [
                    'id' => 10,
                    'is_active' => 1,
                    'percent' => '10.21',
                ],
                'postData' => [
                    'email' => 'test@mail.com',
                    'birthday' => '2000-01-01 12:00:00'
                ],
                'invalidKeys' => ['percent']
            ],
            'invalid_data_values' => [
                'queryData' => [
                    'id' => 5,
                    'is_active' => 0,
                ],
                'postData' => [
                    'email' => 'some string',
                    'percent' => 45.4,
                    'birthday' => '1990-12-24 06:34:00'
                ],
                'invalidKeys' => ['email', 'percent']
            ],
            'invalid_data_types' => [
                'queryData' => [
                    'id' => 'some id',
                    'is_active' => 2,
                ],
                'postData' => [
                    'email' => 10,
                    'percent' => 45.42,
                    'birthday' => true
                ],
                'invalidKeys' => ['id', 'is_active']
            ],
            'empty_data_values' => [
                'queryData' => [
                    'id' => 10,
                    'is_active' => 1,
                ],
                'postData' => [
                    'email' => 'test@mail.com',
                ],
                'invalidKeys' => ['percent', 'birthday']
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
