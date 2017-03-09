<?php

namespace JumpGate\Database\Traits\Model;

/**
 * Class OrderScopes
 *
 * @package JumpGate\Database\Traits
 *
 * @method orderByCreatedAsc()
 * @method orderByCreatedDesc()
 * @method orderByAsc()
 * @method orderByDesc()
 */
trait OrderByScopes
{
    /**
     * Order by created_at ascending scope.
     *
     * @param $query The current query to append to
     */
    public function scopeOrderByCreatedAsc($query)
    {
        return $query->orderBy('created_at', 'asc');
    }
    /**
     * Order by created_at descending scope.
     *
     * @param $query The current query to append to
     */
    public function scopeOrderByCreatedDesc($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Order by name ascending scope.
     *
     * @param $query The current query to append to
     */
    public function scopeOrderByNameAsc($query)
    {
        return $query->orderBy('name', 'asc');
    }

    /**
     * Order by name descending scope.
     *
     * @param $query The current query to append to
     */
    public function scopeOrderByNameDesc($query)
    {
        return $query->orderBy('name', 'desc');
    }
}
