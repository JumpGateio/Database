<?php

namespace JumpGate\Database\Collections;

use Illuminate\Database\Eloquent\Collection;
use JumpGate\Database\Traits\Chaining;
use JumpGate\Database\Traits\Searching;

/**
 * Class Collection
 *
 * This class adds some magic to the collection class.
 * It allows you to tab through collections into other object or collections.
 * It also allows you to run a getWhere on a collection to find objects.
 *
 * @package JumpGate\Database
 *
 * @method getWhere(string $column, string $values)
 * @method getWhereNot(string $column, string $values)
 *
 * @method getWhereIn(string $column, array $values)
 * @method getWhereInFirst(string $column, array $values)
 * @method getWhereInLast(string $column, array $values)
 * @method getWhereNotIn(string $column, array $values)
 *
 * @method getWhereBetween(string $column, array $values)
 * @method getWhereNotBetween(string $column, array $values)
 *
 * @method getWhereLike(string $column, string $values)
 * @method getWhereNotLike(string $column, string $values)
 *
 * @method getWhereNull(string $column)
 * @method getWhereNotNull(string $column)
 *
 * @method getWhereMany(array $column)
 */
class EloquentCollection extends Collection
{
    /**
     * Allow relationship chaining.
     */
    use Chaining;

    /**
     * Add get where searching to collection
     */
    use Searching;

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        // Chaining
        return $this->chainingGetMethod($key);
    }


    /**
     * Allow a method to be run on the entire collection.
     *
     * @param string $method
     * @param array  $args
     *
     * @return Collection
     */
    public function __call($method, $args)
    {

        // No data in the collection.
        if ($this->count() <= 0) {
            return $this;
        }

        // Look for magic where calls.
        if (strstr($method, 'getWhere')) {
            return $this->searchingCallMethod($method, $args);
        }

        // Run the command on each object in the collection.
        return $this->chainingCallMethod($method, $args);
    }

    /**
     * Insert into an object
     *
     * Should be able to do this with methods
     * that already exist on collection.
     *
     * @param mixed $value
     * @param int   $afterKey
     *
     * @return Collection
     */
    public function insertAfter($value, $afterKey)
    {
        $new_object = new self();

        foreach ((array)$this->items as $k => $v) {
            if ($afterKey == $k) {
                $new_object->add($value);
            }

            $new_object->add($v);
        }

        $this->items = $new_object->items;

        return $this;
    }

    /**
     * Turn a collection into a drop down for an html select element.
     *
     * @param  string $firstOptionText Text for the first object in the select array.
     * @param  string $id              The column to use for the id column in the option element.
     * @param  string $name            The column to use for the name column in the option element.
     *
     * @return array                    The new select element array.
     */
    public function toSelectArray($firstOptionText = 'Select one', $id = 'id', $name = 'name')
    {
        $selectArray = [];

        if ($firstOptionText != false) {
            $selectArray[0] = $firstOptionText;
        }

        foreach ($this->items as $item) {
            $selectArray[$item->{$id}] = $item->{$name};
        }

        return $selectArray;
    }
}
