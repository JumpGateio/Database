<?php

namespace JumpGate\Database\Console\Commands\Conversions;

use JumpGate\Database\Traits\Console\ProgressBarTrait;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;

abstract class Conversion extends Command
{
    use ProgressBarTrait;

    /**
     * @var \Illuminate\Database\DatabaseManager
     */
    protected $db;

    /**
     * Batch updates into small sets to avoid memory leaks.
     *
     * @var int
     */
    protected $batchSize = 500;

    /**
     * @var null|string
     */
    protected $originalTableName = null;

    /**
     * @var null|string
     */
    protected $newTableName = null;

    /**
     * @var null|string
     */
    protected $modelName = null;

    /**
     * Create a new command instance.
     *
     * @param \Illuminate\Database\DatabaseManager $db
     */
    public function __construct(DatabaseManager $db)
    {
        parent::__construct();

        $this->db = $db;
    }

    /**
     * If set, truncate the database table.
     *
     * @param string  $table
     * @param boolean $clearLinks
     */
    protected function clearData($table, $clearLinks = false)
    {
        if ($this->option('clear')) {
            $this->db->statement('SET FOREIGN_KEY_CHECKS=0;');

            $this->db->table($table)->truncate();

            if ($clearLinks) {
                $this->db->table('table_links')->where('new_table', $table)->delete();
            }

            $this->db->statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Get a property from an object.  Return the value if it exists.
     *
     * @param $object
     * @param $key
     *
     * @return null
     */
    protected function getProperty($object, $key)
    {
        if (isset($object->{$key}) &&
            (
                (is_int($object->{$key}) && $object->{$key} != 0) ||
                (is_string($object->{$key}) && $object->{$key} != '')
            )
        ) {
            return $object->{$key};
        }

        return null;
    }

    /**
     * Get a property from an array.  Return the value if it exists.
     *
     * @param $array
     * @param $key
     *
     * @return null
     */
    protected function getElement($array, $key)
    {
        if (isset($array[$key]) && $array[$key] != 0 && $array[$key] != '') {
            return $array[$key];
        }

        return null;
    }

    /**
     * When a record has a modified date, make sure one is set.
     *
     * @param $object
     *
     * @return mixed
     */
    protected function getModifiedDate($object)
    {
        if (! isset($object->modified)) {
            return date('Y-m-d h:i:s');
        }

        return $object->modified === '0000-00-00 00:00:00'
            ? date('Y-m-d h:i:s')
            : $object->modified;
    }

    /**
     * Find a new table id based on the old table and id.
     *
     * @param string  $originalTable
     * @param integer $originalId
     *
     * @return mixed
     */
    protected function findLink($originalTable, $originalId)
    {
        $link = $this->db->table('table_links')
                         ->where('original_table', $originalTable)
                         ->where('original_id', $originalId)
                         ->first();

        if (is_null($link)) {
            $this->error('Could not find a link from ' . $originalTable . ' with an id of ' . $originalId);

            return false;
        }

        return $link->new_id;
    }

    protected function getOriginalTable()
    {
        if (is_null($this->originalTableName)) {
            throw new \Exception('You need to set the original table name on ' . get_called_class() . '.');
        }

        return $this->originalTableName;
    }

    protected function getNewTable()
    {
        if (is_null($this->newTableName)) {
            throw new \Exception('You need to set the new table name on ' . get_called_class() . '.');
        }

        return $this->newTableName;
    }

    protected function getModelName()
    {
        if (is_null($this->modelName)) {
            throw new \Exception('You need to set the model name on ' . get_called_class() . '.');
        }

        return $this->modelName;
    }
}
