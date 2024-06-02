<?php

declare(strict_types=1);

namespace Tests\Unit;

use DataValidator\DataManager;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\DTO\DefaultDTO;
use Tests\TestCase;

final class DefaultDtoTest extends TestCase
{
    private ?DataManager $dataManager = null;

    /**
     * @param array<string, mixed> $postData
     *
     * @return void
     */
    #[Test]
    #[DataProvider('validDefaultDtoProvider')]
    public function validDefaultDto(array $postData): void
    {
        $req = new Request(request: $postData);

        $dto = $this->dataManager->validateAndConvert(from: $req, to: DefaultDTO::class);

        $this->assertInstanceOf(expected: DefaultDTO::class, actual: $dto);
        $this->assertSame(expected: $postData['id'], actual: $dto->id);
        $this->assertSame(expected: $postData['email'], actual: $dto->email);
        $this->assertSame(expected: $postData['birthday'], actual: $dto->birthday);
        $this->assertTrue($postData['is_active'] == $dto->isActive);
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
    #[DataProvider('invalidDefaultDtoProvider')]
    public function invalidDefaultDto(array $queryData, array $postData, array $invalidKeys): void
    {
        $req = new Request(query: $queryData, request: $postData);

        try {
            $this->dataManager->validateAndConvert(from: $req, to: DefaultDTO::class);
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
    public static function validDefaultDtoProvider(): array
    {
        return [
            'set_1' => [
                'postData' => [
                    'id' => 10,
                    'email' => 'test@mail.com',
                    'is_active' => 1,
                    'percent' => '10.21',
                    'birthday' => '2000-01-01 12:00:00',
                ],

            ],
            'set_2' => [
                'postData' => [
                    'id' => 5,
                    'email' => 'test_test@mail.com',
                    'is_active' => 0,
                    'percent' => -45.45,
                    'birthday' => '1990-12-24 06:34:00',
                ],

            ],
        ];
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: array<string, mixed>, 2: list<string>}>
     */
    public static function invalidDefaultDtoProvider(): array
    {
        return [
            'data_in_wrong_place' => [
                'queryData' => [
                    'id' => 10,
                    'email' => 'test@mail.com',
                ],
                'postData' => [
                    'is_active' => 1,
                    'percent' => '10.21',
                    'birthday' => '2000-01-01 12:00:00'
                ],
                'invalidKeys' => ['id', 'email']
            ],
            'invalid_data_values' => [
                'queryData' => [],
                'postData' => [
                    'id' => 5,
                    'email' => 'some string',
                    'is_active' => 0,
                    'percent' => 45.4,
                    'birthday' => '1990-12-24 06:34:00'
                ],
                'invalidKeys' => ['email', 'percent']
            ],
            'invalid_data_types' => [
                'queryData' => [],
                'postData' => [
                    'id' => 'some id',
                    'email' => 10,
                    'is_active' => true,
                    'percent' => 45.42,
                    'birthday' => true
                ],
                'invalidKeys' => ['id', 'email', 'birthday']
            ],
            'empty_data_values' => [
                'queryData' => [],
                'postData' => [
                    'id' => 10,
                    'email' => 'test@mail.com',
                    'is_active' => true,
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
