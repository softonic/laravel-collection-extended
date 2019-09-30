<?php
/**
 * Class GroupByHierarchyTest.
 *
 * @package Softonic\Laravel\Collection
 * @author  Jose Manuel Cardona <josemanuel.cardona@softonic.com>
 */

namespace Softonic\Laravel\Collection;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class ServiceProviderTest extends TestCase
{
    public function setUp(): void
    {
        $applicationMock = \Mockery::mock(Application::class);

        (new ServiceProvider($applicationMock))->register();
    }

    public function collectionHierarchiesProvider()
    {
        return [
            'String 1 level'          => [
                'collection'     => collect([
                    [
                        'field_1' => 'key_1',
                        'field_2' => 'item_1',
                    ],
                ]),
                'hierarchy'      => 'field_1',
                'expectedResult' => collect([
                    'key_1' => collect([
                        [
                            'field_1' => 'key_1',
                            'field_2' => 'item_1',
                        ],
                    ]),
                ]),
            ],
            'Array 1 level'           => [
                'collection'     => collect([
                    [
                        'field_1' => 'key_1',
                        'field_2' => 'item_1',
                    ],
                ]),
                'hierarchy'      => ['field_1'],
                'expectedResult' => collect([
                    'key_1' => collect([
                        [
                            'field_1' => 'key_1',
                            'field_2' => 'item_1',
                        ],
                    ]),
                ]),
            ],
            'Multi-items 1 level'     => [
                'collection'     => collect([
                    [
                        'field_1' => 'key_a1',
                        'field_2' => 'item_1',
                    ],
                    [
                        'field_1' => 'key_a1',
                        'field_2' => 'item_2',
                    ],
                    [
                        'field_1' => 'key_b1',
                        'field_2' => 'item_3',
                    ],
                ]),
                'hierarchy'      => ['field_1'],
                'expectedResult' => collect([
                    'key_a1' => collect([
                        [
                            'field_1' => 'key_a1',
                            'field_2' => 'item_1',
                        ],
                        [
                            'field_1' => 'key_a1',
                            'field_2' => 'item_2',
                        ],
                    ]),
                    'key_b1' => collect([
                        [
                            'field_1' => 'key_b1',
                            'field_2' => 'item_3',
                        ],
                    ]),
                ]),
            ],
            'Array multi level'       => [
                'collection'     => collect([
                    [
                        'field_1' => 'key_1',
                        'field_2' => 'key_2',
                        'field_3' => 'key_3',
                        'field_4' => 'item_1',
                    ],
                ]),
                'hierarchy'      => ['field_1', 'field_2', 'field_3'],
                'expectedResult' => collect([
                    'key_1' => collect([
                        'key_2' => collect([
                            'key_3' => collect([
                                [
                                    'field_1' => 'key_1',
                                    'field_2' => 'key_2',
                                    'field_3' => 'key_3',
                                    'field_4' => 'item_1',
                                ],
                            ]),
                        ]),
                    ]),
                ]),
            ],
            'Multi-items multi level' => [
                'collection'     => collect([
                    [
                        'field_1' => 'key_a1',
                        'field_2' => 'key_a2',
                        'field_3' => 'key_a3',
                        'field_4' => 'item_1',
                    ],
                    [
                        'field_1' => 'key_a1',
                        'field_2' => 'key_a2',
                        'field_3' => 'key_a3',
                        'field_4' => 'item_2',
                    ],
                    [
                        'field_1' => 'key_a1',
                        'field_2' => 'key_b2',
                        'field_3' => 'key_b3',
                        'field_4' => 'item_3',
                    ],
                    [
                        'field_1' => 'key_a1',
                        'field_2' => 'key_b2',
                        'field_3' => 'key_c3',
                        'field_4' => 'item_4',
                    ],
                ]),
                'hierarchy'      => ['field_1', 'field_2', 'field_3'],
                'expectedResult' => collect([
                    'key_a1' => collect([
                        'key_a2' => collect([
                            'key_a3' => collect([
                                [
                                    'field_1' => 'key_a1',
                                    'field_2' => 'key_a2',
                                    'field_3' => 'key_a3',
                                    'field_4' => 'item_1',
                                ],
                                [
                                    'field_1' => 'key_a1',
                                    'field_2' => 'key_a2',
                                    'field_3' => 'key_a3',
                                    'field_4' => 'item_2',
                                ],
                            ]),
                        ]),
                        'key_b2' => collect([
                            'key_b3' => collect([
                                [
                                    'field_1' => 'key_a1',
                                    'field_2' => 'key_b2',
                                    'field_3' => 'key_b3',
                                    'field_4' => 'item_3',
                                ],
                            ]),
                            'key_c3' => collect([
                                [
                                    'field_1' => 'key_a1',
                                    'field_2' => 'key_b2',
                                    'field_3' => 'key_c3',
                                    'field_4' => 'item_4',
                                ],
                            ]),

                        ]),
                    ]),
                ]),
            ],
        ];
    }

    /**
     * @test
     * @dataProvider collectionHierarchiesProvider
     *
     * @param Collection $collection
     * @param mixed      $hierarchy
     * @param Collection $expectedResult
     */
    public function it_builds_the_corresponding_hierarchy(
        Collection $collection,
        $hierarchy,
        Collection $expectedResult
    ) {
        $hierarchiedCollection = $collection->groupByHierarchy($hierarchy);
        $this->assertEquals($expectedResult, $hierarchiedCollection);
    }

    /**
     * @test
     */
    public function it_works_with_multiple_parameters()
    {
        $collection     = collect([
            [
                'field_1' => 'key_1',
                'field_2' => 'key_2',
                'field_3' => 'item_1',
            ],
        ]);
        $expectedResult = collect([
            'key_1' => collect([
                'key_2' => collect([
                    [
                        'field_1' => 'key_1',
                        'field_2' => 'key_2',
                        'field_3' => 'item_1',
                    ],
                ]),
            ]),
        ]);

        $hierarchiedCollection = $collection->groupByHierarchy('field_1', 'field_2');
        $this->assertEquals($expectedResult, $hierarchiedCollection);
    }

    /**
     * @test
     */
    public function it_throws_a_runtime_exception_if_the_field_does_not_exist()
    {
        $collection = collect([
            [
                'field_2' => 'item_1',
            ],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Grouping error: 'field_1' doesn't exist in collection");

        $collection->groupByHierarchy('field_1');
    }

    public function collectionExtractProvider()
    {
        return [
            'Single column'       => [
                'collection'     => collect([
                    [
                        'field_1' => 'key_1',
                        'field_2' => 'item_1',
                    ],
                ]),
                'fields'         => 'field_2',
                'expectedResult' => collect([
                    collect([
                        'field_2' => 'item_1',
                    ]),
                ]),
            ],
            'Multi column'        => [
                'collection'     => collect([
                    [
                        'field_1' => 'key_1',
                        'field_2' => 'item_1',
                        'field_3' => 'item_3',
                    ],
                ]),
                'fields'         => ['field_1', 'field_3'],
                'expectedResult' => collect([
                    collect([
                        'field_1' => 'key_1',
                        'field_3' => 'item_3',
                    ]),
                ]),
            ],
            'Multi column deeper' => [
                'collection'     => collect([
                    [
                        'field_1' => ['value' => 'key_1'],
                        'field_2' => ['value' => 'item_1'],
                        'field_3' => ['value' => 'item_3'],
                    ],
                ]),
                'fields'         => ['field_1.value', 'field_3.value'],
                'expectedResult' => collect([
                    collect([
                        'field_1.value' => 'key_1',
                        'field_3.value' => 'item_3',
                    ]),
                ]),
            ],

            'Multi column deeper with alias' => [
                'collection'     => collect([
                    [
                        'field_1' => ['value' => 'key_1'],
                        'field_2' => ['value' => 'item_1'],
                        'field_3' => ['value' => 'item_3'],
                    ],
                ]),
                'fields'         => [['field_1' => 'field_1.value'], ['field_3' => 'field_3.value']],
                'expectedResult' => collect([
                    collect([
                        'field_1' => 'key_1',
                        'field_3' => 'item_3',
                    ]),
                ]),
            ],

            'Multi column deeper with conflicts' => [
                'collection'     => collect([
                    [
                        'field_1'       => ['value' => 'key_1'],
                        'field_1.value' => 'item_1',
                        'field_3'       => ['value' => 'item_3'],
                    ],
                ]),
                'fields'         => 'field_1.value',
                'expectedResult' => collect([
                    collect([
                        'field_1.value' => 'item_1',
                    ]),
                ]),
            ],
        ];
    }

    /**
     * @test
     * @dataProvider collectionExtractProvider
     *
     * @param Collection $collection
     * @param mixed      $fields
     * @param Collection $expectedResult
     */
    public function it_extracts_the_corresponding_fields(
        Collection $collection,
        $fields,
        Collection $expectedResult
    ) {
        $extractedCollection = $collection->extract($fields);
        $this->assertEquals($expectedResult, $extractedCollection);
    }
}
