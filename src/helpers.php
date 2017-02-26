<?php

use JumpGate\Database\Collections\EloquentCollection;
use JumpGate\Database\Collections\SupportCollection;

if (! function_exists('collector')) {
    /**
     * Create a collection from the given value.
     *
     * @param  mixed $value
     *
     * @return \JumpGate\Database\Collections\EloquentCollection
     */
    function collector($value = null)
    {
        return new EloquentCollection($value);
    }
}

if (! function_exists('supportCollector')) {
    /**
     * Create a collection from the given value.
     *
     * @param  mixed $value
     *
     * @return \JumpGate\Database\Collections\SupportCollection
     */
    function supportCollector($value = null)
    {
        return new SupportCollection($value);
    }
}
