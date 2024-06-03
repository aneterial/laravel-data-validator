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

final class BaseValidationTest extends TestCase
{
    private ?DataManager $dataManager = null;

    /**
     * @param array<string, mixed> $queryData
     * @param array<string, mixed> $postData
     * @param array<string, string> $queryRules
     * @param array<string, string> $postRules
     * @param bool $expectException
     * @param array<string, mixed> $data
     *
     * @return void
     */
    #[Test]
    #[DataProvider('validateProvider')]
    public function validate(
        array $queryData,
        array $postData,
        array $queryRules,
        array $postRules,
        bool $expectException,
        array $data
    ): void {
        if ($expectException) {
            $this->expectException(ValidationException::class);
        }

        $req = new Request(query: $queryData, request: $postData);

        $result = $this->dataManager->validate(request: $req, queryRules: $queryRules, bodyRules: $postRules);

        if (!$expectException) {
            $this->assertSame(expected: $data, actual: $result);
        }
    }

    /**
     * @param array<string, mixed> $postData
     *
     * @return void
     */
    #[Test]
    #[DataProvider('validateAndConvertProvider')]
    public function validateAndConvert(array $postData): void
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
     * @param array<string, mixed> $postData
     *
     * @return void
     */
    #[Test]
    #[DataProvider('validateAndConvertListProvider')]
    public function validateAndConvertList(array $postData): void
    {
        $req = new Request(request: $postData);

        $dtos = $this->dataManager->validateAndConvertList(from: $req, to: DefaultDTO::class);

        foreach ($dtos as $k => $dto) {
            $this->assertInstanceOf(expected: DefaultDTO::class, actual: $dto);
            $this->assertSame(expected: $postData[$k]['id'], actual: $dto->id);
            $this->assertSame(expected: $postData[$k]['email'], actual: $dto->email);
            $this->assertSame(expected: $postData[$k]['birthday'], actual: $dto->birthday);
            $this->assertTrue($postData[$k]['is_active'] == $dto->isActive);
            $this->assertTrue($postData[$k]['percent'] == $dto->percent);
        }
    }

    /**
     * @param array<string, mixed> $queryData
     * @param array<string, mixed> $postData
     * @param list<string> $invalidKeys
     *
     * @return void
     */
    #[Test]
    #[DataProvider('validateAndConvertInvalidProvider')]
    public function validateAndConvertInvalid(array $queryData, array $postData, array $invalidKeys): void
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
     * @param array<string, mixed> $queryData
     * @param array<string, mixed> $postData
     * @param list<string> $invalidKeys
     *
     * @return void
     */
    #[Test]
    #[DataProvider('validateAndConvertInvalidListProvider')]
    public function validateAndConvertInvalidList(array $queryData, array $postData, array $invalidKeys): void
    {
        $req = new Request(query: $queryData, request: $postData);

        try {
            $this->dataManager->validateAndConvertList(from: $req, to: DefaultDTO::class);
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
    public static function validateAndConvertProvider(): array
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
     * @return array<string, array{0: array<string, mixed>}>
     */
    public static function validateAndConvertListProvider(): array
    {
        return [
            'set_1' => [
                'postData' => [
                    [
                        'id' => 10,
                        'email' => 'test@mail.com',
                        'is_active' => 1,
                        'percent' => '10.21',
                        'birthday' => '2000-01-01 12:00:00',
                    ],
                    [
                        'id' => 24,
                        'email' => 'test_2@mail.com',
                        'is_active' => true,
                        'percent' => '45.23',
                        'birthday' => '2006-06-04 12:00:00',
                    ],
                ],

            ],
            'set_2' => [
                'postData' => [
                    [
                        'id' => 5,
                        'email' => 'test_test@mail.com',
                        'is_active' => 0,
                        'percent' => -45.45,
                        'birthday' => '1990-12-24 06:34:00',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: array<string, mixed>, 2: list<string>}>
     */
    public static function validateAndConvertInvalidProvider(): array
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
    /**
     * @return array<string, array{0: array<string, mixed>, 1: array<string, mixed>, 2: list<string>}>
     */
    public static function validateAndConvertInvalidListProvider(): array
    {
        return [
            'invalid_data_values' => [
                'queryData' => [],
                'postData' => [
                    [
                        'id' => 5,
                        'email' => 'some string',
                        'is_active' => 0,
                        'percent' => 45.4,
                        'birthday' => '1990-12-24 06:34:00'
                    ],
                    [
                        'id' => 5,
                        'email' => 'test@mail.com',
                        'is_active' => 7,
                        'percent' => 45.41,
                        'birthday' => 3
                    ],
                ],
                'invalidKeys' => ['0.email', '1.is_active', '0.percent', '1.birthday']
            ],
            'invalid_data_types' => [
                'queryData' => [],
                'postData' => [
                    [
                        'id' => 'some id',
                        'email' => 10,
                        'is_active' => true,
                        'percent' => 45.42,
                        'birthday' => true
                    ],
                    true
                ],
                'invalidKeys' => ['0.id', '1.id', '0.email', '1.email', '1.is_active', '1.percent', '0.birthday', '1.birthday']
            ],
            'empty_data_values' => [
                'queryData' => [],
                'postData' => [
                    [
                        'id' => 10,
                        'email' => 'test@mail.com',
                        'is_active' => true,
                    ],
                ],
                'invalidKeys' => ['0.percent', '0.birthday']
            ],
        ];
    }

    /**
     * @return array<string, array{0:array<string, mixed>, 1:array<string, mixed>, 2:array<string, string>, 3:array<string, string>, 4: bool, 5:array<string, mixed>}>
     */
    public static function validateProvider(): array
    {
        return [
            'invalid_post' => [
                'queryData' => ['isBool' => true, 'uid' => 'A4567BXC'],
                'postData' => ['id' => -4, 'name' => 'Test'],
                'queryRules' => ['isBool' => 'required|boolean', 'uid' => 'required|string|alpha_dash|min:3'],
                'postRules' => ['id' => 'required|integer|min:0', 'name' => 'required|string|alpha_dash|min:3'],
                'expectException' => true,
                'data' => [],
            ],
            'invalid_query' => [
                'queryData' => ['isBool' => 'Test', 'name' => 5],
                'postData' => ['id' => 4],
                'queryRules' => ['isBool' => 'required|boolean', 'name' => 'required|string|alpha_dash|min:3'],
                'postRules' => ['id' => 'required|integer|min:0'],
                'expectException' => true,
                'data' => [],
            ],
            'invalid_all' => [
                'queryData' => ['isBool' => true, 'uid' => 456],
                'postData' => ['id' => -4, 'name' => 'Test'],
                'queryRules' => ['isBool' => 'required|boolean', 'uid' => 'required|string|alpha_dash|min:3'],
                'postRules' => ['id' => 'required|integer|min:0', 'name' => 'required|string|alpha_dash|min:3'],
                'expectException' => true,
                'data' => [],
            ],
            'valid_post' => [
                'queryData' => [],
                'postData' => ['id' => 4, 'name' => 'Test'],
                'queryRules' => [],
                'postRules' => ['id' => 'required|integer|min:0', 'name' => 'required|string|alpha_dash|min:3'],
                'expectException' => false,
                'data' => ['id' => 4, 'name' => 'Test'],
            ],
            'valid_query' => [
                'queryData' => ['isBool' => true, 'uid' => 'A4567BXC'],
                'postData' => [],
                'queryRules' => ['isBool' => 'required|boolean', 'uid' => 'required|string|alpha_dash|min:3'],
                'postRules' => [],
                'expectException' => false,
                'data' => ['isBool' => true, 'uid' => 'A4567BXC'],
            ],
            'valid_all' => [
                'queryData' => ['isBool' => true, 'uid' => 'A4567BXC'],
                'postData' => ['id' => 4, 'name' => 'Test'],
                'queryRules' => ['isBool' => 'required|boolean', 'uid' => 'required|string|alpha_dash|min:3'],
                'postRules' => ['id' => 'required|integer|min:0', 'name' => 'required|string|alpha_dash|min:3'],
                'expectException' => false,
                'data' => ['isBool' => true, 'uid' => 'A4567BXC', 'id' => 4, 'name' => 'Test'],
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
