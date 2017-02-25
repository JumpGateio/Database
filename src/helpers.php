<?php

use JumpGate\Database\Collections\EloquentCollection;

if (! function_exists('collector')) {
    /**
     * Create a collection from the given value.
     *
     * @param  mixed $value
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function collector($value = null)
    {
        return new EloquentCollection($value);
    }
}
