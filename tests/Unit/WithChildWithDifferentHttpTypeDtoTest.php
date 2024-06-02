<?php

declare(strict_types=1);

namespace Tests\Unit;

use DataValidator\DataManager;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\DTO\WithChildWithDifferentHttpTypeDTO;
use Tests\DTO\WithDifferentHttpTypeDTO;
use Tests\TestCase;

final class WithChildWithDifferentHttpTypeDtoTest extends TestCase
{
    private ?DataManager $dataManager = null;

    /**
     * @param array<string, mixed> $queryData
     * @param array<string, mixed> $postData
     *
     * @return void
     */
    #[Test]
    #[DataProvider('validWithChildWithDifferentHttpTypeDtoProvider')]
    public function validWithChildWithDifferentHttpTypeDto(array $queryData, array $postData): void
    {
        $req = new Request(query: $queryData, request: $postData);

        $dto = $this->dataManager->validateAndConvert(from: $req, to: WithChildWithDifferentHttpTypeDTO::class);

        $this->assertInstanceOf(expected: WithChildWithDifferentHttpTypeDTO::class, actual: $dto);
        $this->assertSame(expected: $queryData['uid'], actual: $dto->uid);

        $this->assertInstanceOf(expected: WithDifferentHttpTypeDTO::class, actual: $dto->child);
        $this->assertSame(expected: $postData['child']['id'], actual: $dto->child->id);
        $this->assertSame(expected: $postData['child']['email'], actual: $dto->child->email);
        $this->assertSame(expected: $postData['child']['birthday'], actual: $dto->child->birthday);
        $this->assertTrue($postData['child']['is_active'] == $dto->child->isActive);
        $this->assertTrue($postData['child']['percent'] == $dto->child->percent);
    }

    /**
     * @param array<string, mixed> $queryData
     * @param array<string, mixed> $postData
     * @param list<string> $invalidKeys
     *
     * @return void
     */
    #[Test]
    #[DataProvider('invalidWithChildWithDifferentHttpTypeDtoProvider')]
    public function invalidWithChildWithDifferentHttpTypeDto(array $queryData, array $postData, array $invalidKeys): void
    {
        $req = new Request(query: $queryData, request: $postData);

        try {
            $this->dataManager->validateAndConvert(from: $req, to: WithChildWithDifferentHttpTypeDTO::class);
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
    public static function validWithChildWithDifferentHttpTypeDtoProvider(): array
    {
        return [
            'set_1' => [
                'queryData' => [
                    'uid' => 101,
                ],
                'postData' => [
                    'child' => [
                        'id' => 10,
                        'is_active' => 1,
                        'email' => 'test@mail.com',
                        'percent' => '10.21',
                        'birthday' => '2000-01-01 12:00:00',
                    ],
                ],

            ],
            'set_2' => [
                'queryData' => [
                    'uid' => 17,
                ],
                'postData' => [
                    'child' => [
                        'id' => 7878,
                        'is_active' => 0,
                        'email' => 'test_test@mail.com',
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
    public static function invalidWithChildWithDifferentHttpTypeDtoProvider(): array
    {
        return [
            'data_in_wrong_place_1' => [
                'queryData' => [
                    'uid' => 10,
                    'child' => [
                        'is_active' => 1,
                    ],
                ],
                'postData' => [
                    'child' => [
                        'id' => 7878,
                        'email' => 'test@mail.com',
                        'percent' => '10.21',
                        'birthday' => '2000-01-01 12:00:00'
                    ],
                ],
                'invalidKeys' => ['child.is_active']
            ],
            'data_in_wrong_place_2' => [
                'queryData' => [
                    'uid' => 10,
                    'child' => [
                        'percent' => '10.21',
                    ],
                ],
                'postData' => [
                    'child' => [
                        'id' => 7878,
                        'is_active' => 1,
                        'email' => 'test@mail.com',
                        'birthday' => '2000-01-01 12:00:00'
                    ],
                ],
                'invalidKeys' => ['child.percent']
            ],
            'invalid_data_values' => [
                'queryData' => [
                    'uid' => 5,
                ],
                'postData' => [
                    'child' => [
                        'id' => 34,
                        'is_active' => 0,
                        'email' => 'some string',
                        'percent' => 45.4,
                        'birthday' => '1990-12-24 06:34:00'
                    ],
                ],
                'invalidKeys' => ['child.email', 'child.percent']
            ],
            'invalid_data_types' => [
                'queryData' => [
                    'uid' => 6,
                ],
                'postData' => [
                    'child' => [
                        'id' => 4545,
                        'email' => 10,
                        'is_active' => 2,
                        'percent' => 45.42,
                        'birthday' => true
                    ],
                ],
                'invalidKeys' => ['child.email', 'child.is_active', 'child.birthday']
            ],
            'empty_data_values' => [
                'queryData' => [
                    'uid' => 10,
                ],
                'postData' => [
                    'child' => [
                        'is_active' => 1,
                        'email' => 'test@mail.com',
                    ]
                ],
                'invalidKeys' => ['child.id', 'child.percent', 'child.birthday']
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
