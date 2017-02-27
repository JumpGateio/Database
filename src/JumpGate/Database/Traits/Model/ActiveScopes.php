<?php

namespace JumpGate\Database\Traits\Models;

/**
 * Class ActiveScopes
 *
 * @package JumpGate\Database\Traits
 *
 * @method active()
 * @method inactive()
 */
trait ActiveScopes
{
    /**
     * Get only active rows.
     *
     * @param $query The current query to append to
     */
    public function scopeActive($query)
    {
        return $query->where('activeFlag', 1);
    }

    /**
     * Get only inactive rows.
     *
     * @param $query The current query to append to
     */
    public function scopeInactive($query)
    {
        return $query->where('activeFlag', 0);
    }
}
