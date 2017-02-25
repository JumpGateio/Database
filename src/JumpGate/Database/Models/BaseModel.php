<?php

namespace JumpGate\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use JumpGate\Database\Collections\EloquentCollection;
use JumpGate\Database\Traits\ActiveScopes;
use JumpGate\Database\Traits\OrderByScopes;

/**
 * @property string $uniqueId
 */
abstract class BaseModel extends Model
{
    use ActiveScopes, OrderByScopes;

    /**
     * Whether the model should return JumpGate\Database\Collection or
     * Illuminate\Database\Eloquent\Collection.
     *
     * @var boolean
     */
    protected $jumpGateCollections = true;

    /**
     * Assign as observer to use
     *
     * @var string
     */
    protected static $observer = null;

    /**
     * Use the custom collection that allows tapping.
     *
     * @param array $models An array of models to turn into a collection.
     *
     * @return \Illuminate\Database\Eloquent\Collection OR \JumpGate\Database\Collections\EloquentCollection
     */
    public function newCollection(array $models = [])
    {
        if ($this->jumpGateCollections) {
            return new EloquentCollection($models);
        }

        return parent::newCollection($models);
    }

    /********************************************************************
     * Model events
     *******************************************************************/

    /**
     * Common tasks needed for all models.
     * Registers the observer if it exists.
     * Sets the default creating event to check for uniqueIds when the model uses them.
     */
    public static function boot()
    {
        parent::boot();

        $class = get_called_class();

        if (method_exists($class, 'handleUniqueColumns')) {
            self::handleUniqueColumns();
        }

        if (static::$observer != null) {
            $class::observe(new static::$observer);
        }
    }
}
