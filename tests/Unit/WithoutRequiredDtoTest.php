<?php

declare(strict_types=1);

namespace Tests\Unit;

use DataValidator\ValidationManager;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\DTO\WithoutRequiredDTO;
use Tests\TestCase;

final class WithoutRequiredDtoTest extends TestCase
{
    private ?ValidationManager $validationManager = null;

    /**
     * @param array<string, mixed> $postData
     *
     * @return void
     */
    #[Test]
    #[DataProvider('validWithoutRequiredDtoProvider')]
    public function validWithoutRequiredDto(array $postData): void
    {
        $req = new Request(request: $postData);

        $dto = $this->validationManager->validateAndHydrate(request: $req, class: WithoutRequiredDTO::class);

        $this->assertInstanceOf(expected: WithoutRequiredDTO::class, actual: $dto);
        $this->assertSame(expected: $postData['id'] ?? null, actual: $dto->id);
        $this->assertSame(expected: $postData['email'] ?? null, actual: $dto->email);
        $this->assertSame(expected: $postData['birthday'] ?? null, actual: $dto->birthday);
        $this->assertTrue(($postData['is_active'] ?? null) == $dto->isActive);
        $this->assertTrue(($postData['percent'] ?? null) == $dto->percent);
        $this->assertSame(expected: $postData['codes'] ?? [], actual: $dto->codes);
    }

    /**
     * @param array<string, mixed> $queryData
     * @param array<string, mixed> $postData
     * @param list<string> $invalidKeys
     *
     * @return void
     */
    #[Test]
    #[DataProvider('invalidWithoutRequiredDtoProvider')]
    public function invalidWithoutRequiredDto(array $queryData, array $postData, array $invalidKeys): void
    {
        $req = new Request(query: $queryData, request: $postData);

        try {
            $this->validationManager->validateAndHydrate(request: $req, class: WithoutRequiredDTO::class);
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
    public static function validWithoutRequiredDtoProvider(): array
    {
        return [
            'set_1' => [
                'postData' => [
                    'id' => 10,
                    'email' => 'test@mail.com',
                    'is_active' => 1,
                    'percent' => '10.21',
                    'birthday' => '2000-01-01 12:00:00',
                    'codes' => [1, 34, 9999, -1234]
                ],
            ],
            'set_2' => [
                'postData' => [
                    'id' => 10,
                    'email' => 'test@mail.com',
                    'is_active' => 1,
                    'percent' => '10.21',
                    'birthday' => '2000-01-01 12:00:00',
                    'codes' => []
                ],
            ],
            'set_3' => [
                'postData' => [
                    'id' => 5,
                    'email' => 'test_test@mail.com',
                    'is_active' => 0,
                    'percent' => -45.45,
                    'birthday' => '1990-12-24 06:34:00',
                ],
            ],
            'set_4' => [
                'postData' => [
                    'email' => 'test_test@mail.com',
                    'is_active' => 0,
                    'percent' => -45.45,
                    'birthday' => '1990-12-24 06:34:00',
                ],
            ],
            'set_5' => [
                'postData' => [
                    'email' => 'test_test@mail.com',
                    'percent' => -45.45,
                    'birthday' => '1990-12-24 06:34:00',
                ],
            ],
            'set_6' => [
                'postData' => [
                    'percent' => -45.45,
                    'birthday' => '1990-12-24 06:34:00',
                ],
            ],
            'set_7' => [
                'postData' => [
                    'percent' => -45.45,
                ],
            ],
            'set_8' => [
                'postData' => [],
            ],
        ];
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: array<string, mixed>, 2: list<string>}>
     */
    public static function invalidWithoutRequiredDtoProvider(): array
    {
        return [
            'invalid_data_values' => [
                'queryData' => [],
                'postData' => [
                    'id' => 5,
                    'email' => 'some string',
                    'is_active' => 0,
                    'percent' => 45.4,
                    'birthday' => '1990-12-24 06:34:00',
                    'codes' => [1, 34, 9999, 0]
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
                    'birthday' => true,
                    'codes' => [1, 34, 9999, -1234, 'hello world']
                ],
                'invalidKeys' => ['id', 'email', 'birthday', 'codes.4']
            ],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->validationManager = $this->app->make(ValidationManager::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->validationManager = null;
    }
}
