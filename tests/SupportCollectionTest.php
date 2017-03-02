<?php

namespace Tests;

use JumpGate\Database\Collections\SupportCollection;
use Orchestra\Testbench\TestCase;
use stdClass;

class SupportCollectionTest extends TestCase
{
    /**
     * @var \JumpGate\Database\Collections\SupportCollection
     */
    protected $collection;

    public function setUp()
    {
        parent::setUp();

        $this->collection = new SupportCollection;

        $testData =
            [
                [
                    'name' => 'bob',
                    'age'  => 10,
                    'kids' => [
                        'name' => 'zack',
                        'age'  => 2,
                    ],
                ],
                [
                    'name' => 'jeff',
                    'age'  => 15,
                    'kids' => [
                        'name' => 'jess',
                        'age'  => 3,
                    ],
                ],
                [
                    'name' => 'chris',
                    'age'  => 20,
                    'kids' => [
                        'name' => 'jr',
                        'age'  => 4,
                    ],
                ],
                [
                    'name' => 'dug',
                    'age'  => 25,
                    'kids' => [
                        'name' => 'dan',
                        'age'  => 5,
                    ],
                ],
                [
                    'name' => 'sam',
                    'age'  => null,
                    'kids' => [
                        'name' => 'jane',
                        'age'  => null,
                    ],
                ],
            ];

        foreach ($testData as $data) {
            $newParent       = new stdClass;
            $newParent->name = $data['name'];
            $newParent->age  = $data['age'];

            $newParent->kids = new SupportCollection;
            $newChild        = new stdClass;
            $newChild->name  = $data['kids']['name'];
            $newChild->age   = $data['kids']['age'];
            $newParent->kids->add($newChild);
            $this->collection->add($newParent);
        }
    }

    /** @test */
    public function it_gets_results_where_key_is_equal_to_given_value()
    {
        $this->assertCount(1, $this->collection->getWhere('name', 'bob'));
    }

    /** @test */
    public function it_gets_results_that_are_in_a_given_array()
    {
        $this->assertCount(2, $this->collection->getWhereIn('age', [10, 15]));
    }

    /** @test */
    public function it_gets_results_between_two_given_values()
    {
        $this->assertCount(3, $this->collection->getWhereBetween('age', [10, 20]));
    }

    /** @test */
    public function it_gets_results_when_key_is_null()
    {
        $this->assertCount(1, $this->collection->getWhereNull('age'));
    }

    /** @test */
    public function it_gets_results_where_key_is_similar_to_given_value()
    {
        $this->assertCount(1, $this->collection->getWhereLike('name', 'hri'));
    }

    /** @test */
    public function it_gets_results_where_key_is_not_equal_to_given_value()
    {
        $this->assertCount(4, $this->collection->getWhereNot('name', 'bob'));
    }

    /** @test */
    public function it_gets_results_that_are_not_in_a_given_array()
    {
        $this->assertCount(2, $this->collection->getWhereNotIn('age', [10, 15]));
    }

    /** @test */
    public function it_gets_results_that_are_not_between_two_given_values()
    {
        $this->assertCount(1, $this->collection->getWhereNotBetween('age', [10, 20]));
    }

    /** @test */
    public function it_gets_results_when_the_key_is_not_null()
    {
        $this->assertCount(4, $this->collection->getWhereNotNull('age'));
    }

    /** @test */
    public function it_gets_results_where_key_is_not_similar_to_given_value()
    {
        $this->assertCount(4, $this->collection->getWhereNotLike('name', 'hri'));
    }

    /** @test */
    public function it_gets_the_first_result_in_a_given_array()
    {
        $data = $this->collection->getWhereInFirst('age', [10, 15]);

        $this->assertInstanceOf(stdClass::class, $data);
        $this->assertEquals(10, $data->age);
    }

    /** @test */
    public function it_gets_the_last_result_in_a_given_array()
    {
        $data = $this->collection->getWhereInLast('age', [10, 15]);

        $this->assertInstanceOf(stdClass::class, $data);
        $this->assertEquals(15, $data->age);
    }

    /** @test */
    public function it_taps_through_the_given_key_and_checks_the_given_value()
    {
        $data = $this->collection->getWhere('kids->name', 'jess');

        $this->assertCount(1, $data);
        $this->assertEquals('jeff', $data->first()->name);
    }

    /** @test */
    public function it_taps_through_the_given_key_and_checks_in_array()
    {
        $this->assertCount(2, $this->collection->getWhereIn('kids->age', [2, 4]));
    }

    /** @test */
    public function it_taps_through_the_given_key_and_checks_between_two_given_values()
    {
        $this->assertCount(3, $this->collection->getWhereBetween('kids->age', [2, 4]));
    }

    /** @test */
    public function it_taps_through_the_given_key_and_checks_for_null()
    {
        $this->assertCount(1, $this->collection->getWhereNull('kids->age'));
    }

    /** @test */
    public function it_taps_through_the_given_key_and_checks_for_similar()
    {
        $this->assertCount(1, $this->collection->getWhereLike('kids->name', 'es'));
    }

    /** @test */
    public function it_taps_through_the_given_key_and_checks_not_the_given_value()
    {
        $this->assertCount(4, $this->collection->getWhereNot('kids->name', 'dan'));
    }

    /** @test */
    public function it_taps_through_the_given_key_and_checks_not_in_array()
    {
        $this->assertCount(2, $this->collection->getWhereNotIn('kids->age', [2, 4]));
    }

    /** @test */
    public function it_taps_through_the_given_key_and_checks_not_between_two_given_values()
    {
        $this->assertCount(1, $this->collection->getWhereNotBetween('kids->age', [2, 4]));
    }

    /** @test */
    public function it_taps_through_the_given_key_and_checks_for_not_null()
    {
        $this->assertCount(4, $this->collection->getWhereNotNull('kids->age'));
    }

    /** @test */
    public function it_taps_through_the_given_key_and_checks_for_not_similar()
    {
        $this->assertCount(4, $this->collection->getWhereNotLike('kids->name', 'es'));
    }

    /** @test */
    public function it_taps_through_the_object_to_find_the_first_with_a_given_value()
    {
        $data = $this->collection->getWhereInFirst('kids->age', [2, 4]);

        $this->assertInstanceOf(stdClass::class, $data);
        $this->assertEquals(10, $data->age);
    }

    /** @test */
    public function it_taps_through_the_object_to_find_the_last_with_a_given_value()
    {
        $data = $this->collection->getWhereInLast('kids->age', [2, 4]);

        $this->assertInstanceOf(stdClass::class, $data);
        $this->assertEquals(20, $data->age);
    }

    /** @test */
    public function it_inserts_an_item_after_another()
    {
        $collection = new SupportCollection([
            'testing',
            'inserting',
            'after',
            'this',
            'should',
            'work'
        ]);

        $collection->insertAfter('TESTING', 3);

        $this->assertCount(7, $collection);
        $this->assertEquals('TESTING', $collection->get(4));
    }

    /** @test */
    public function it_inserts_an_item_before_another()
    {
        $collection = new SupportCollection([
            'testing',
            'inserting',
            'after',
            'this',
            'should',
            'work'
        ]);

        $collection->insertBefore('TESTING', 3);

        $this->assertCount(7, $collection);
        $this->assertEquals('TESTING', $collection->get(3));
    }

    /** @test */
    public function it_converts_a_collection_to_an_array_usable_by_select_drop_downs()
    {
        $collection = new SupportCollection([
            (object)[
                'id' => 1,
                'name' => 'Tom'
            ],
            (object)[
                'id' => 2,
                'name' => 'Bill'
            ],
            (object)[
                'id' => 3,
                'name' => 'Katy'
            ],
        ]);
        $array = $collection->toSelectArray('Select One', 'id', 'name');

        $this->assertTrue(is_array($array));
        $this->assertCount(4, $array);
        $this->assertEquals('Select One', head($array));
    }

    /** @test */
    public function it_taps_through_a_collection_and_retrieves_a_collection()
    {
        $data = $this->collection->first()->kids->name;

        $this->assertInstanceOf(\JumpGate\Database\Collections\SupportCollection::class, $data);
        $this->assertEquals('zack', $data->first());
        $this->assertCount(1, $data);
    }

    /** @test */
    public function it_explodes_a_string_and_returns_a_collection()
    {
        $string     = 'Testing exploding a string';
        $collection = SupportCollection::explode(' ', $string);

        $this->assertCount(4, $collection);
        $this->assertInstanceOf(SupportCollection::class, $collection);
    }

    /** @test */
    public function is_parses_multiple_types_to_a_collection()
    {
        $commaString = SupportCollection::parseMixed(',foo,bar,baz,', ',|+');
        $pipeString  = SupportCollection::parseMixed('foo|bar|baz', ',|+');
        $plusString  = SupportCollection::parseMixed('foo+bar+baz', ',|+');
        $allString   = SupportCollection::parseMixed('foo,|+bar+baz', ',|+');
        $array       = SupportCollection::parseMixed(['foo', 'bar', 'baz']);

        // All values should come out equal.
        $this->assertTrue(($commaString->values() == $pipeString->values()));
        $this->assertTrue(($pipeString->values() == $plusString->values()));
        $this->assertTrue(($plusString->values() == $array->values()));
        $this->assertTrue(($array->values() == $allString->values()));

        // All values should return a collection.
        $this->assertInstanceOf(SupportCollection::class, $commaString);
        $this->assertInstanceOf(SupportCollection::class, $pipeString);
        $this->assertInstanceOf(SupportCollection::class, $plusString);
        $this->assertInstanceOf(SupportCollection::class, $allString);
        $this->assertInstanceOf(SupportCollection::class, $array);

        // All values should have the same number of items.
        $this->assertCount(3, $commaString);
        $this->assertCount(3, $pipeString);
        $this->assertCount(3, $plusString);
        $this->assertCount(3, $allString);
        $this->assertCount(3, $array);
    }
}
