<?php

namespace JumpGate\Database\Traits;

trait Chainable {

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        $newCollection = new self();

        foreach ($this->items as $item) {
            // This item is a collection
            if ($item instanceof self) {
                foreach ($item as $subItem) {
                    $newCollection->put($newCollection->count(), $subItem->$key);
                }

                continue;
            }

            // The next item in the chain is a collection
            if (is_object($item) && !$item instanceof self && $item->$key instanceof self) {
                foreach ($item->$key as $subItem) {
                    $newCollection->put($newCollection->count(), $subItem);
                }

                continue;
            }

            $newCollection->put($newCollection->count(), $item->$key);
        }

        return $newCollection;
    }

    /**
     * @param $item
     * @param $column
     * @param $value
     * @param $operator
     * @param $inverse
     *
     * @return bool
     */
    private function handleMultiTap($item, $column, $value, $operator, $inverse)
    {
        list($objectToSearch, $columnToSearch) = $this->tapThroughObjects($column, $item);

        if ($objectToSearch instanceof self) {
            foreach ($objectToSearch as $subObject) {
                // The column has a tap that ends in a collection.
                return $this->whereObject($subObject, $columnToSearch, $operator, $value, $inverse);
            }
        } else {
            // The column has a tap that ends in direct access
            return $this->whereObject($objectToSearch, $columnToSearch, $operator, $value, $inverse);
        }
    }

    /**
     * @param $column
     * @param $item
     *
     * @return mixed
     */
    private function tapThroughObjects($column, $item)
    {
        $taps = explode('->', $column);

        $objectToSearch = $item;
        $columnToSearch = array_pop($taps);

        foreach ($taps as $tapKey => $tap) {
            // Keep tapping till we hit the last object.
            $objectToSearch = $objectToSearch->$tap;
        }

        return [$objectToSearch, $columnToSearch];
    }
}