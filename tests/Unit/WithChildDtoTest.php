<?php

declare(strict_types=1);

namespace Tests\Unit;

use DataValidator\DataManager;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\DTO\DefaultDTO;
use Tests\DTO\WithChildDTO;
use Tests\TestCase;

final class WithChildDtoTest extends TestCase
{
    private ?DataManager $dataManager = null;

    /**
     * @param array<string, mixed> $postData
     *
     * @return void
     */
    #[Test]
    #[DataProvider('validWithChildDtoProvider')]
    public function validWithChildDto(array $postData): void
    {
        $req = new Request(request: $postData);

        $dto = $this->dataManager->validateAndConvert(from: $req, to: WithChildDTO::class);

        $this->assertInstanceOf(expected: WithChildDTO::class, actual: $dto);
        $this->assertSame(expected: $postData['uid'], actual: $dto->uid);

        $this->assertInstanceOf(expected: DefaultDTO::class, actual: $dto->child);
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
    #[DataProvider('invalidWithChildDtoProvider')]
    public function invalidWithChildDto(array $queryData, array $postData, array $invalidKeys): void
    {
        $req = new Request(query: $queryData, request: $postData);

        try {
            $this->dataManager->validateAndConvert(from: $req, to: WithChildDTO::class);
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
    public static function validWithChildDtoProvider(): array
    {
        return [
            'set_1' => [
                'postData' => [
                    'uid' => 1,
                    'child' => [
                        'id' => 10,
                        'email' => 'test@mail.com',
                        'is_active' => 1,
                        'percent' => '10.21',
                        'birthday' => '2000-01-01 12:00:00',
                    ],
                ],
            ],
            'set_2' => [
                'postData' => [
                    'uid' => 10,
                    'child' => [
                        'id' => 5,
                        'email' => 'test_test@mail.com',
                        'is_active' => false,
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
    public static function invalidWithChildDtoProvider(): array
    {
        return [
            'data_in_wrong_place' => [
                'queryData' => [
                    'uid' => 10,
                    'child' => [
                        'id' => 1,
                        'email' => 'test@mail.com',
                    ],
                ],
                'postData' => [
                    'child' => [
                        'is_active' => 1,
                        'percent' => '10.21',
                        'birthday' => '2000-01-01 12:00:00'
                    ],
                ],
                'invalidKeys' => ['uid', 'child.id', 'child.email']
            ],
            'invalid_data_values' => [
                'queryData' => [],
                'postData' => [
                    'uid' => 12,
                    'child' => [
                        'id' => 5,
                        'email' => 'some string',
                        'is_active' => 0,
                        'percent' => 45.4,
                        'birthday' => '1990-12-24 06:34:00'
                    ],
                ],
                'invalidKeys' => ['child.email', 'child.percent']
            ],
            'invalid_data_types' => [
                'queryData' => [],
                'postData' => [
                    'uid' => 'hello',
                    'child' => [
                        'id' => 'some id',
                        'email' => 10,
                        'is_active' => true,
                        'percent' => 45.42,
                        'birthday' => true
                    ],
                ],
                'invalidKeys' => ['uid', 'child.id', 'child.email', 'child.birthday']
            ],
            'empty_data_values' => [
                'queryData' => [],
                'postData' => [
                    'uid' => 67,
                    'child' => [
                        'id' => 10,
                        'email' => 'test@mail.com',
                        'is_active' => true,
                    ],
                ],
                'invalidKeys' => ['child.percent', 'child.birthday']
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
