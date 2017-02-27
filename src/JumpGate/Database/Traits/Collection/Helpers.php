<?php

namespace JumpGate\Database\Traits\Collection;

trait Helpers
{
    /**
     * Explode a string and return a collection.
     *
     * @param string $delimiter
     * @param string $string
     * @param int    $limit
     *
     * @return $this
     */
    public static function explode($delimiter, $string, $limit = null)
    {
        $array = explode($delimiter, $string);

        if (! is_null($limit)) {
            $array = explode($delimiter, $string, $limit);
        }

        return new static($array);
    }
}
