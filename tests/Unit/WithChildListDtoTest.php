<?php

declare(strict_types=1);

namespace Tests\Unit;

use DataValidator\DataManager;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\DTO\DefaultDTO;
use Tests\DTO\WithChildListDTO;
use Tests\TestCase;

final class WithChildListDtoTest extends TestCase
{
    private ?DataManager $dataManager = null;

    /**
     * @param array<string, mixed> $postData
     *
     * @return void
     */
    #[Test]
    #[DataProvider('validWithChildListDtoProvider')]
    public function validWithChildListDto(array $postData): void
    {
        $req = new Request(request: $postData);

        $dto = $this->dataManager->validateAndConvert(from: $req, to: WithChildListDTO::class);

        $this->assertInstanceOf(expected: WithChildListDTO::class, actual: $dto);
        $this->assertSame(expected: $postData['uid'], actual: $dto->uid);
        $this->assertIsList($dto->children);

        foreach ($dto->children as $index => $childDto) {
            $this->assertInstanceOf(expected: DefaultDTO::class, actual: $childDto);
            $this->assertSame(expected: $postData['children'][$index]['id'], actual: $childDto->id);
            $this->assertSame(expected: $postData['children'][$index]['email'], actual: $childDto->email);
            $this->assertSame(expected: $postData['children'][$index]['birthday'], actual: $childDto->birthday);
            $this->assertTrue($postData['children'][$index]['is_active'] == $childDto->isActive);
            $this->assertTrue($postData['children'][$index]['percent'] == $childDto->percent);
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
    #[DataProvider('invalidWithChildListDtoProvider')]
    public function invalidWithChildListDto(array $queryData, array $postData, array $invalidKeys): void
    {
        $req = new Request(query: $queryData, request: $postData);

        try {
            $this->dataManager->validateAndConvert(from: $req, to: WithChildListDTO::class);
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
    public static function validWithChildListDtoProvider(): array
    {
        return [
            'set_1' => [
                'postData' => [
                    'uid' => 1,
                    'children' => [
                        [
                            'id' => 10,
                            'email' => 'test1@mail.com',
                            'is_active' => 1,
                            'percent' => '10.21',
                            'birthday' => '2000-01-01 12:00:00',
                        ],
                        [
                            'id' => 11,
                            'email' => 'test2@mail.com',
                            'is_active' => true,
                            'percent' => '10.31',
                            'birthday' => '2000-01-01 12:00:00',
                        ],
                    ],
                ],
            ],
            'set_2' => [
                'postData' => [
                    'uid' => 10,
                    'children' => [
                        [
                            'id' => 51,
                            'email' => 'test1_test@mail.com',
                            'is_active' => 0,
                            'percent' => -45.45,
                            'birthday' => '1990-12-24 06:34:00',
                        ],
                        [
                            'id' => 53,
                            'email' => 'test2_test@mail.com',
                            'is_active' => false,
                            'percent' => -45.45,
                            'birthday' => '1990-12-24 06:34:00',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: array<string, mixed>, 2: list<string>}>
     */
    public static function invalidWithChildListDtoProvider(): array
    {
        return [
            'data_in_wrong_place' => [
                'queryData' => [
                    'children' => [
                        [
                            'id' => 1,
                            'email' => 'test@mail.com',
                        ],
                        [
                            'is_active' => 1,
                            'birthday' => '2000-01-01 12:00:00',

                        ],
                    ],
                ],
                'postData' => [
                    'uid' => 10,
                    'children' => [
                        [
                            'is_active' => 1,
                            'percent' => '10.21',
                            'birthday' => '2000-01-01 12:00:00'
                        ],
                        [
                            'id' => 1,
                            'percent' => '10.21',
                            'email' => 'test@mail.com',
                        ],
                    ],
                ],
                'invalidKeys' => ['children.0.id', 'children.0.email', 'children.1.is_active', 'children.1.birthday']
            ],
            'invalid_data_values' => [
                'queryData' => [],
                'postData' => [
                    'uid' => 12,
                    'children' => [
                        [
                            'id' => 5,
                            'email' => 'some string',
                            'is_active' => 0,
                            'percent' => 45.4,
                            'birthday' => '1990-12-24 06:34:00'
                        ],
                        [
                            'id' => 123,
                            'email' => 'test@mail.com',
                            'is_active' => 1,
                            'percent' => 45.42,
                            'birthday' => '19902222-12-24 06:34:00'
                        ],
                    ],
                ],
                'invalidKeys' => ['children.0.email', 'children.0.percent', 'children.1.birthday']
            ],
            'invalid_data_types' => [
                'queryData' => [],
                'postData' => [
                    'uid' => 4,
                    'children' => [
                        [
                            'id' => 'some id',
                            'email' => 10,
                            'is_active' => true,
                            'percent' => 45.42,
                            'birthday' => true
                        ],
                        [
                            'id' => 23,
                            'email' => 'test@mail.com',
                            'is_active' => true,
                            'percent' => 45.42,
                            'birthday' => 4444
                        ],
                    ],
                ],
                'invalidKeys' => ['children.0.id', 'children.0.email', 'children.0.birthday', 'children.1.birthday']
            ],
            'empty_data_values' => [
                'queryData' => [],
                'postData' => [
                    'uid' => 67,
                    'children' => [
                        [
                            'id' => 10,
                            'email' => 'test@mail.com',
                            'is_active' => true,
                        ],
                        [
                            'birthday' => '1990-12-24 06:34:00'
                        ],
                    ],
                ],
                'invalidKeys' => ['children.1.id', 'children.1.email', 'children.1.is_active', 'children.0.percent', 'children.1.percent', 'children.0.birthday',]
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
