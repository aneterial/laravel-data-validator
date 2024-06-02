<?php

declare(strict_types=1);

namespace Tests\Unit;

use DataValidator\DataManager;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Data\ColorEnum;
use Tests\DTO\DefaultDTO;
use Tests\DTO\ExtremeNestingDTO;
use Tests\DTO\NestingDTO;
use Tests\DTO\WithChildListDTO;
use Tests\DTO\WithListsDTO;
use Tests\TestCase;

final class ExtremeNestingDtoTest extends TestCase
{
    private ?DataManager $dataManager = null;

    /**
     * @param array<string, mixed> $postData
     *
     * @return void
     */
    #[Test]
    #[DataProvider('validExtremeNestingDtoProvider')]
    public function validExtremeNestingDto(array $postData): void
    {
        $req = new Request(request: $postData);

        $dto = $this->dataManager->validateAndConvert(from: $req, to: ExtremeNestingDTO::class);

        $this->assertInstanceOf(expected: ExtremeNestingDTO::class, actual: $dto);

        $this->assertInstanceOf(expected: NestingDTO::class, actual: $dto->child);
        $this->assertSame(expected: $postData['child']['id'], actual: $dto->child->id);
        $this->assertSame(expected: $postData['child']['ids'], actual: $dto->child->ids);
        $this->assertSame(expected: $postData['child']['child']['uid'], actual: $dto->child->child->uid);

        foreach ($dto->child->child->children as $i => $defaultDTO) {
            $this->assertInstanceOf(expected: DefaultDTO::class, actual: $defaultDTO);
            $this->assertSame(expected: $postData['child']['child']['children'][$i]['id'], actual: $defaultDTO->id);
            $this->assertSame(expected: $postData['child']['child']['children'][$i]['email'], actual: $defaultDTO->email);
            $this->assertTrue($postData['child']['child']['children'][$i]['is_active'] == $defaultDTO->isActive);
            $this->assertTrue($postData['child']['child']['children'][$i]['percent'] == $defaultDTO->percent);
            $this->assertSame(expected: $postData['child']['child']['children'][$i]['birthday'], actual: $defaultDTO->birthday);
        }

        foreach ($dto->child->children as $i => $withChildListDTO) {
            $this->assertInstanceOf(expected: WithChildListDTO::class, actual: $withChildListDTO);
            $this->assertSame(expected: $postData['child']['children'][$i]['uid'], actual: $withChildListDTO->uid);

            foreach ($withChildListDTO->children as $j => $defaultDTO) {
                $this->assertInstanceOf(expected: DefaultDTO::class, actual: $defaultDTO);
                $this->assertSame(expected: $postData['child']['children'][$i]['children'][$j]['id'], actual: $defaultDTO->id);
                $this->assertSame(expected: $postData['child']['children'][$i]['children'][$j]['email'], actual: $defaultDTO->email);
                $this->assertTrue($postData['child']['children'][$i]['children'][$j]['is_active'] == $defaultDTO->isActive);
                $this->assertTrue($postData['child']['children'][$i]['children'][$j]['percent'] == $defaultDTO->percent);
                $this->assertSame(expected: $postData['child']['children'][$i]['children'][$j]['birthday'], actual: $defaultDTO->birthday);
            }
        }

        foreach ($dto->child->lists as $i => $list) {
            $this->assertInstanceOf(expected: WithListsDTO::class, actual: $list);
            $this->assertSame(expected: $postData['child']['lists'][$i]['cities'], actual: $list->cities);
            $this->assertSame(expected: $postData['child']['lists'][$i]['codes'], actual: $list->codes);
            $this->assertSame(expected: $postData['child']['lists'][$i]['emails'], actual: $list->emails);
            $this->assertSame(expected: $postData['child']['lists'][$i]['ids'], actual: $list->ids);
            $this->assertSame(expected: $postData['child']['lists'][$i]['dates'], actual: $list->dates);
        }

        $this->assertSame(expected: $postData['child']['color'], actual: $dto->child->color->value);
        $this->assertSame(expected: $postData['child']['colors'], actual: array_map(static fn (ColorEnum $c): string => $c->value, $dto->child->colors));

        foreach ($dto->children as $index => $nestingDTO) {
            $this->assertInstanceOf(expected: NestingDTO::class, actual: $nestingDTO);
            $this->assertSame(expected: $postData['children'][$index]['id'], actual: $nestingDTO->id);
            $this->assertSame(expected: $postData['children'][$index]['ids'], actual: $nestingDTO->ids);
            $this->assertSame(expected: $postData['children'][$index]['child']['uid'], actual: $nestingDTO->child->uid);

            foreach ($nestingDTO->child->children as $i => $defaultDTO) {
                $this->assertInstanceOf(expected: DefaultDTO::class, actual: $defaultDTO);
                $this->assertSame(expected: $postData['children'][$index]['child']['children'][$i]['id'], actual: $defaultDTO->id);
                $this->assertSame(expected: $postData['children'][$index]['child']['children'][$i]['email'], actual: $defaultDTO->email);
                $this->assertTrue($postData['children'][$index]['child']['children'][$i]['is_active'] == $defaultDTO->isActive);
                $this->assertTrue($postData['children'][$index]['child']['children'][$i]['percent'] == $defaultDTO->percent);
                $this->assertSame(expected: $postData['children'][$index]['child']['children'][$i]['birthday'], actual: $defaultDTO->birthday);
            }

            foreach ($nestingDTO->children as $i => $withChildListDTO) {
                $this->assertInstanceOf(expected: WithChildListDTO::class, actual: $withChildListDTO);
                $this->assertSame(expected: $postData['children'][$index]['children'][$i]['uid'], actual: $withChildListDTO->uid);

                foreach ($withChildListDTO->children as $j => $defaultDTO) {
                    $this->assertInstanceOf(expected: DefaultDTO::class, actual: $defaultDTO);
                    $this->assertSame(expected: $postData['children'][$index]['children'][$i]['children'][$j]['id'], actual: $defaultDTO->id);
                    $this->assertSame(expected: $postData['children'][$index]['children'][$i]['children'][$j]['email'], actual: $defaultDTO->email);
                    $this->assertTrue($postData['children'][$index]['children'][$i]['children'][$j]['is_active'] == $defaultDTO->isActive);
                    $this->assertTrue($postData['children'][$index]['children'][$i]['children'][$j]['percent'] == $defaultDTO->percent);
                    $this->assertSame(expected: $postData['children'][$index]['children'][$i]['children'][$j]['birthday'], actual: $defaultDTO->birthday);
                }
            }

            foreach ($nestingDTO->lists as $i => $list) {
                $this->assertInstanceOf(expected: WithListsDTO::class, actual: $list);
                $this->assertSame(expected: $postData['children'][$index]['lists'][$i]['cities'], actual: $list->cities);
                $this->assertSame(expected: $postData['children'][$index]['lists'][$i]['codes'], actual: $list->codes);
                $this->assertSame(expected: $postData['children'][$index]['lists'][$i]['emails'], actual: $list->emails);
                $this->assertSame(expected: $postData['children'][$index]['lists'][$i]['ids'], actual: $list->ids);
                $this->assertSame(expected: $postData['children'][$index]['lists'][$i]['dates'], actual: $list->dates);
            }

            $this->assertSame(expected: $postData['children'][$index]['color'], actual: $nestingDTO->color->value);
            $this->assertSame(expected: $postData['children'][$index]['colors'], actual: array_map(static fn (ColorEnum $c): string => $c->value, $nestingDTO->colors));
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
    #[DataProvider('invalidExtremeNestingDtoProvider')]
    public function invalidExtremeNestingDto(array $queryData, array $postData, array $invalidKeys): void
    {
        $req = new Request(query: $queryData, request: $postData);

        try {
            $this->dataManager->validateAndConvert(from: $req, to: ExtremeNestingDTO::class);
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
    public static function validExtremeNestingDtoProvider(): array
    {
        return [
            'all_valid' => [
                'postData' => [
                    'child' => [
                        'id' => 10,
                        'ids' => [11,12,13],
                        'child' => [
                            'uid' => 145,
                            'children' => [
                                [
                                    'id' => 4546,
                                    'email' => 'test1@mail.com',
                                    'is_active' => 1,
                                    'percent' => '10.21',
                                    'birthday' => '2000-01-01 12:00:00',
                                ],
                                [
                                    'id' => 2234,
                                    'email' => 'test2@mail.com',
                                    'is_active' => 1,
                                    'percent' => '10.21',
                                    'birthday' => '2000-01-01 12:00:00',
                                ],
                            ]
                        ],
                        'children' => [
                            [
                                'uid' => 145,
                                'children' => [
                                    [
                                        'id' => 4546,
                                        'email' => 'test3@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                    [
                                        'id' => 2234,
                                        'email' => 'test4@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                ]
                            ],
                            [
                                'uid' => 145,
                                'children' => [
                                    [
                                        'id' => 4546,
                                        'email' => 'test5@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                    [
                                        'id' => 2234,
                                        'email' => 'test6@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                ],
                            ],
                        ],
                        'lists' => [
                            [
                                'cities' => ['Moscow', 'St Petersburg'],
                                'codes' => [-23, 45, 1234],
                                'emails' => ['test7@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                                'ids' => [10, 11, 12, 13],
                                'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                            ],
                            [
                                'cities' => ['Moscow', 'St Petersburg'],
                                'codes' => [-23, 45, 1234],
                                'emails' => ['test8@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                                'ids' => [10, 11, 12, 13],
                                'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                            ],
                        ],
                        'color' => '#ffffff',
                        'colors' => ['#ffffff', '#000000'],
                    ],
                    'children' => [
                        [
                            'id' => 10,
                            'ids' => [11,12,13],
                            'child' => [
                                'uid' => 145,
                                'children' => [
                                    [
                                        'id' => 4546,
                                        'email' => 'test9@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                    [
                                        'id' => 2234,
                                        'email' => 'test10@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                ]
                            ],
                            'children' => [
                                [
                                    'uid' => 145,
                                    'children' => [
                                        [
                                            'id' => 4546,
                                            'email' => 'test11@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                        [
                                            'id' => 2234,
                                            'email' => 'test12@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                    ]
                                ],
                                [
                                    'uid' => 145,
                                    'children' => [
                                        [
                                            'id' => 4546,
                                            'email' => 'test13@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                        [
                                            'id' => 2234,
                                            'email' => 'test14@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                    ],
                                ],
                            ],
                            'lists' => [
                                [
                                    'cities' => ['Moscow', 'St Petersburg'],
                                    'codes' => [-23, 45, 1234],
                                    'emails' => ['test15@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                                    'ids' => [10, 11, 12, 13],
                                    'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                                ],
                                [
                                    'cities' => ['Moscow', 'St Petersburg'],
                                    'codes' => [-23, 45, 1234],
                                    'emails' => ['test16@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                                    'ids' => [10, 11, 12, 13],
                                    'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                                ],
                            ],
                            'color' => '#ffffff',
                            'colors' => ['#ffffff', '#000000'],
                        ],
                        [
                            'id' => 10,
                            'ids' => [11,12,13],
                            'child' => [
                                'uid' => 145,
                                'children' => [
                                    [
                                        'id' => 4546,
                                        'email' => 'test17@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                    [
                                        'id' => 2234,
                                        'email' => 'test18@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                ]
                            ],
                            'children' => [
                                [
                                    'uid' => 145,
                                    'children' => [
                                        [
                                            'id' => 4546,
                                            'email' => 'test19@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                        [
                                            'id' => 2234,
                                            'email' => 'test29@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                    ]
                                ],
                                [
                                    'uid' => 145,
                                    'children' => [
                                        [
                                            'id' => 4546,
                                            'email' => 'test21@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                        [
                                            'id' => 2234,
                                            'email' => 'test22@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                    ],
                                ],
                            ],
                            'lists' => [
                                [
                                    'cities' => ['Moscow', 'St Petersburg'],
                                    'codes' => [-23, 45, 1234],
                                    'emails' => ['test25@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                                    'ids' => [10, 11, 12, 13],
                                    'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                                ],
                                [
                                    'cities' => ['Moscow', 'St Petersburg'],
                                    'codes' => [-23, 45, 1234],
                                    'emails' => ['test24@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                                    'ids' => [10, 11, 12, 13],
                                    'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                                ],
                            ],
                            'color' => '#ffffff',
                            'colors' => ['#ffffff', '#000000'],
                        ],
                    ]
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: array<string, mixed>, 2: list<string>}>
     */
    public static function invalidExtremeNestingDtoProvider(): array
    {
        return [
            'invalid_data_values' => [
                'queryData' => [],
                'postData' => [
                    'child' => [
                        'id' => 10,
                        'ids' => [11,12,13],
                        'child' => [
                            'uid' => 145,
                            'children' => [
                                [
                                    'id' => 4546,
                                    'email' => 'test1@mail.com',
                                    'is_active' => 1,
                                    'percent' => '10.21',
                                    'birthday' => '2000-01-01 12:00:00',
                                ],
                                [
                                    'id' => 2234,
                                    'email' => 'hell0o',
                                    'is_active' => 1,
                                    'percent' => '10.21',
                                    'birthday' => '2000-01-01 12:00:00',
                                ],
                            ]
                        ],
                        'children' => [
                            [
                                'uid' => 145,
                                'children' => [
                                    [
                                        'id' => 4546,
                                        'email' => 'test3@mail.com',
                                        'is_active' => 3,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                    [
                                        'id' => 2234,
                                        'email' => 'test4@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                ]
                            ],
                            [
                                'uid' => 145,
                                'children' => [
                                    [
                                        'id' => 4546,
                                        'email' => 'test5@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                    [
                                        'id' => 2234,
                                        'email' => 'test6@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                ],
                            ],
                        ],
                        'lists' => [
                            [
                                'cities' => ['Moscow', 'St Petersburg'],
                                'codes' => [-23, 45, 1234],
                                'emails' => ['test7@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                                'ids' => [10, 11, 12, 13],
                                'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                            ],
                            [
                                'cities' => ['Moscow', 'St Petersburg'],
                                'codes' => [-23, 45, 1234],
                                'emails' => ['test8@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                                'ids' => [10, 11, 12, 13],
                                'dates' => ['2000-01-ssss01 12:00:00', '2024-04-23 12:30:00'],
                            ],
                        ],
                        'color' => '#ffffff',
                        'colors' => ['#ffffff', '#000000'],
                    ],
                    'children' => [
                        [
                            'id' => 10,
                            'ids' => [11,12,13],
                            'child' => [
                                'uid' => 145,
                                'children' => [
                                    [
                                        'id' => 4546,
                                        'email' => 'test9@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                    [
                                        'id' => 2234,
                                        'email' => 'test10@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                ]
                            ],
                            'children' => [
                                [
                                    'uid' => 145,
                                    'children' => [
                                        [
                                            'id' => 4546,
                                            'email' => 'test11@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                        [
                                            'id' => 2234,
                                            'email' => 'test12@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                    ]
                                ],
                                [
                                    'uid' => 145,
                                    'children' => [
                                        [
                                            'id' => 4546,
                                            'email' => 'test13@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                        [
                                            'id' => 2234,
                                            'email' => '1sss',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                    ],
                                ],
                            ],
                            'lists' => [
                                [
                                    'cities' => ['Moscow', 'St Petersburg'],
                                    'codes' => [-23, 45, 1234],
                                    'emails' => ['test15@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                                    'ids' => [10, 11, 12, 13],
                                    'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                                ],
                                [
                                    'cities' => ['Moscow', 'St Petersburg'],
                                    'codes' => [-23, 45, 1234],
                                    'emails' => ['test16@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                                    'ids' => [10, 11, 12, 13],
                                    'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                                ],
                            ],
                            'color' => '#ffffff',
                            'colors' => ['#ffffff', 'green'],
                        ],
                        [
                            'id' => 10,
                            'ids' => [11,12,13],
                            'child' => [
                                'uid' => 145,
                                'children' => [
                                    [
                                        'id' => 4546,
                                        'email' => 'test17@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                    [
                                        'id' => 2234,
                                        'email' => 'test18@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                ]
                            ],
                            'children' => [
                                [
                                    'uid' => 145,
                                    'children' => [
                                        [
                                            'id' => 4546,
                                            'email' => 'test19@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                        [
                                            'id' => 2234,
                                            'email' => 'test29@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                    ]
                                ],
                                [
                                    'uid' => 145,
                                    'children' => [
                                        [
                                            'id' => 4546,
                                            'email' => 'test21@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                        [
                                            'id' => 2234,
                                            'email' => 'test22@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                    ],
                                ],
                            ],
                            'lists' => [
                                [
                                    'cities' => ['Moscow', 'St Petersburg'],
                                    'codes' => [-23, 45, 1234],
                                    'emails' => ['test25@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                                    'ids' => [10, 11, 12, 13],
                                    'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                                ],
                                [
                                    'cities' => ['Moscow', 'St Petersburg'],
                                    'codes' => [-23, 45, 1234],
                                    'emails' => ['test24@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                                    'ids' => [10, 11, 12, 13],
                                    'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                                ],
                            ],
                            'color' => 'red',
                            'colors' => ['#ffffff', '#000000'],
                        ],
                    ]
                ],
                'invalidKeys' => [
                    'child.child.children.1.email',
                    'child.children.0.children.0.is_active',
                    'child.lists.1.dates.0',
                    'children.0.children.1.children.1.email',
                    'children.1.color',
                    'children.0.colors.1',
                ]
            ],
            'invalid_data_types' => [
                'queryData' => [],
                'postData' => [
                    'child' => [
                        'id' => 10,
                        'ids' => [11,12,13],
                        'child' => [
                            'uid' => 145,
                            'children' => [
                                [
                                    'id' => true,
                                    'email' => 'test1@mail.com',
                                    'is_active' => 1,
                                    'percent' => '10.21',
                                    'birthday' => '2000-01-01 12:00:00',
                                ],
                                [
                                    'id' => 2234,
                                    'email' => 234,
                                    'is_active' => 1,
                                    'percent' => '10.21',
                                    'birthday' => '2000-01-01 12:00:00',
                                ],
                            ]
                        ],
                        'children' => [
                            [
                                'uid' => 145,
                                'children' => [
                                    [
                                        'id' => 4546,
                                        'email' => 'test3@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                    [
                                        'id' => 2234,
                                        'email' => 'test4@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                ]
                            ],
                            [
                                'uid' => 145,
                                'children' => [
                                    [
                                        'id' => 4546,
                                        'email' => 'test5@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                    [
                                        'id' => 2234,
                                        'email' => 'test6@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                ],
                            ],
                        ],
                        'lists' => [
                            [
                                'cities' => ['Moscow', 'St Petersburg'],
                                'codes' => [-23, 45, 1234],
                                'emails' => ['test7@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                                'ids' => [10, 11, 12, 13],
                                'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                            ],
                            [
                                'cities' => ['Moscow', 'St Petersburg'],
                                'codes' => [-23, 45, 1234],
                                'emails' => ['test8@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                                'ids' => [10, 11, 12, 13],
                                'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                            ],
                        ],
                        'color' => '#ffffff',
                        'colors' => ['#ffffff', '#000000'],
                    ],
                    'children' => [
                        [
                            'id' => 10,
                            'ids' => [11,12,13],
                            'child' => [
                                'uid' => 145,
                                'children' => [
                                    [
                                        'id' => 4546,
                                        'email' => 'test9@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                    [
                                        'id' => 'hello',
                                        'email' => 'test10@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                ]
                            ],
                            'children' => [
                                [
                                    'uid' => 145,
                                    'children' => [
                                        [
                                            'id' => 4546,
                                            'email' => 'test11@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                        [
                                            'id' => 2234,
                                            'email' => 'test12@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                    ]
                                ],
                                [
                                    'uid' => 145,
                                    'children' => [
                                        [
                                            'id' => 4546,
                                            'email' => 'test13@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                        [
                                            'id' => 2234,
                                            'email' => 'test14@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                    ],
                                ],
                            ],
                            'lists' => [
                                [
                                    'cities' => ['Moscow', 'St Petersburg'],
                                    'codes' => [-23, 45, 1234],
                                    'emails' => ['test15@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                                    'ids' => [10, 11, 12, 13],
                                    'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                                ],
                                [
                                    'cities' => ['Moscow', 'St Petersburg'],
                                    'codes' => [-23, 45, 1234],
                                    'emails' => ['test16@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                                    'ids' => [10, 11, 12, 13],
                                    'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                                ],
                            ],
                            'color' => '1',
                            'colors' => ['#ffffff', 12],
                        ],
                        [
                            'id' => 10,
                            'ids' => [11,12,13],
                            'child' => [
                                'uid' => 145,
                                'children' => [
                                    [
                                        'id' => 4546,
                                        'email' => 'test17@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                    [
                                        'id' => 2234,
                                        'email' => 'test18@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                ]
                            ],
                            'children' => [
                                [
                                    'uid' => 145,
                                    'children' => [
                                        [
                                            'id' => 4546,
                                            'email' => 'test19@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                        [
                                            'id' => 2234,
                                            'email' => 'test29@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                    ]
                                ],
                                [
                                    'uid' => 145,
                                    'children' => [
                                        [
                                            'id' => 4546,
                                            'email' => 'test21@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                        [
                                            'id' => 2234,
                                            'email' => 'test22@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                    ],
                                ],
                            ],
                            'lists' => [
                                [
                                    'cities' => ['Moscow', 'St Petersburg'],
                                    'codes' => [-23, 45, 1234],
                                    'emails' => ['test25@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                                    'ids' => [10, 11, 12, 13],
                                    'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                                ],
                                [
                                    'cities' => ['Moscow', 'St Petersburg'],
                                    'codes' => [-23, 45, 1234],
                                    'emails' => ['test24@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                                    'ids' => [10, 11, 12, 13],
                                    'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                                ],
                            ],
                            'color' => '#ffffff',
                            'colors' => ['#ffffff', '#000000'],
                        ],
                    ]
                ],
                'invalidKeys' => [
                    'child.child.children.1.email',
                    'children.0.child.children.1.id',
                    'children.0.color',
                    'children.0.colors.1',
                ]
            ],
            'empty_data_values' => [
                'queryData' => [],
                'postData' => [
                    'child' => [
                        'id' => 10,
                        'ids' => [11,12,13],
                        'child' => [
                            'uid' => 145,
                            'children' => [
                                [
                                    'id' => 4546,
                                    'email' => 'test1@mail.com',
                                    'is_active' => 1,
                                    'birthday' => '2000-01-01 12:00:00',
                                ],
                                [
                                    'id' => 2234,
                                    'email' => 'test2@mail.com',
                                    'is_active' => 1,
                                    'percent' => '10.21',
                                    'birthday' => '2000-01-01 12:00:00',
                                ],
                            ]
                        ],
                        'children' => [
                            [
                                'children' => [
                                    [
                                        'id' => 4546,
                                        'email' => 'test3@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                    [
                                        'id' => 2234,
                                        'email' => 'test4@mail.com',
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                ]
                            ],
                            [
                                'uid' => 145,
                                'children' => [
                                    [
                                        'id' => 4546,
                                        'email' => 'test5@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                    [
                                        'id' => 2234,
                                        'email' => 'test6@mail.com',
                                        'is_active' => 1,
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                ],
                            ],
                        ],
                        'lists' => [
                            [
                                'cities' => ['Moscow', 'St Petersburg'],
                                'codes' => [-23, 45, 1234],
                                'ids' => [10, 11, 12, 13],
                                'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                            ],
                            [
                                'cities' => ['Moscow', 'St Petersburg'],
                                'codes' => [-23, 45, 1234],
                                'emails' => ['test8@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                                'ids' => [10, 11, 12, 13],
                                'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                            ],
                        ],
                        'color' => '#ffffff',
                    ],
                    'children' => [
                        [
                            'id' => 10,
                            'ids' => [11,12,13],
                            'child' => [
                                'uid' => 145,
                                'children' => [
                                    [
                                        'id' => 4546,
                                        'email' => 'test9@mail.com',
                                        'is_active' => 1,
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                    [
                                        'id' => 2234,
                                        'email' => 'test10@mail.com',
                                        'is_active' => 1,
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                ]
                            ],
                            'children' => [
                                [
                                    'uid' => 145,
                                    'children' => [
                                        [
                                            'id' => 4546,
                                            'email' => 'test11@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                        [
                                            'id' => 2234,
                                            'email' => 'test12@mail.com',
                                            'is_active' => 1,
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                    ]
                                ],
                                [
                                    'children' => [
                                        [
                                            'id' => 4546,
                                            'email' => 'test13@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                        [
                                            'id' => 2234,
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                    ],
                                ],
                            ],
                            'lists' => [
                                [
                                    'codes' => [-23, 45, 1234],
                                    'emails' => ['test15@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                                    'ids' => [10, 11, 12, 13],
                                    'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                                ],
                                [
                                    'cities' => ['Moscow', 'St Petersburg'],
                                    'codes' => [-23, 45, 1234],
                                    'emails' => ['test16@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                                    'ids' => [10, 11, 12, 13],
                                    'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                                ],
                            ],
                            'color' => '#ffffff',
                            'colors' => ['#ffffff', '#000000'],
                        ],
                        [
                            'id' => 10,
                            'ids' => [11,12,13],
                            'child' => [
                                'uid' => 145,
                                'children' => [
                                    [
                                        'id' => 4546,
                                        'email' => 'test17@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                    [
                                        'id' => 2234,
                                        'email' => 'test18@mail.com',
                                        'is_active' => 1,
                                        'percent' => '10.21',
                                        'birthday' => '2000-01-01 12:00:00',
                                    ],
                                ]
                            ],
                            'children' => [
                                [
                                    'children' => [
                                        [
                                            'id' => 4546,
                                            'email' => 'test19@mail.com',
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                        [
                                            'id' => 2234,
                                            'email' => 'test29@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                    ]
                                ],
                                [
                                    'uid' => 145,
                                    'children' => [
                                        [
                                            'id' => 4546,
                                            'email' => 'test21@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                            'birthday' => '2000-01-01 12:00:00',
                                        ],
                                        [
                                            'id' => 2234,
                                            'email' => 'test22@mail.com',
                                            'is_active' => 1,
                                            'percent' => '10.21',
                                        ],
                                    ],
                                ],
                            ],
                            'lists' => [
                                [
                                    'cities' => ['Moscow', 'St Petersburg'],
                                    'emails' => ['test25@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                                    'ids' => [10, 11, 12, 13],
                                    'dates' => ['2000-01-01 12:00:00', '2024-04-23 12:30:00'],
                                ],
                                [
                                    'cities' => ['Moscow', 'St Petersburg'],
                                    'codes' => [-23, 45, 1234],
                                    'emails' => ['test24@mail.com', 'test@mail.ru', 'test_1@mail.com'],
                                    'ids' => [10, 11, 12, 13],
                                ],
                            ],
                            'color' => '#ffffff',
                            'colors' => ['#ffffff', '#000000'],
                        ],
                    ]
                ],
                'invalidKeys' => [
                    'child.colors',
                    'child.child.children.0.percent',
                    'child.children.0.uid',
                    'child.children.0.children.1.is_active',
                    'child.children.1.children.1.percent',
                    'child.lists.0.emails',
                    'children.0.child.children.0.percent',
                    'children.0.child.children.1.percent',
                    'children.0.children.1.uid',
                    'children.1.children.0.uid',
                    'children.0.children.1.children.1.email',
                    'children.1.children.0.children.0.is_active',
                    'children.0.children.0.children.1.percent',
                    'children.1.children.1.children.1.birthday',
                    'children.0.lists.0.cities',
                    'children.1.lists.0.codes',
                    'children.1.lists.1.dates',
                ]
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
