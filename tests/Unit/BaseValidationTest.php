<?php

declare(strict_types=1);

namespace Tests\Unit;

use DataValidator\DataManager;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
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
