<?php

namespace JumpGate\Database\Traits;

trait Searching {

    public function searchingCallMethod($method, $args)
    {
        return $this->magicWhere(snake_case($method), $args);
    }

    /**
     * Turn the magic getWhere into a real where query.
     *
     * @param $method
     * @param $args
     *
     * @return Collection
     */
    private function magicWhere($method, $args)
    {
        $whereStatement = explode('_', $method);

        // Get where
        if (count($whereStatement) == 2) {
            return $this->getWhere($args[0], '=', $args[1]);
        }

        $operators = [
            'in', 'between', 'like', 'null',
            'not',
            'first', 'last',
            'many',
        ];

        // If an operator is found then add operators.
        if (array_intersect($whereStatement, $operators)) {
            list($operator, $firstOrLast, $inverse) = $this->determineMagicWhereDetails($whereStatement);

            $column = $args[0];
            $value  = (isset($args[1]) ? $args[1] : null);

            return $this->getWhere(
                $column,
                $operator,
                $value,
                $inverse,
                $firstOrLast
            );
        }
    }

    /**
     * @param $whereStatement
     *
     * @return array
     */
    private function determineMagicWhereDetails($whereStatement)
    {
        $finalOperator = '=';
        $position      = null;
        $not           = false;

        foreach ($whereStatement as $operator) {
            $finalOperator = $this->checkMagicWhereFinalOperator($operator, $finalOperator);
            $position      = $this->checkMagicWherePosition($operator, $position);
            $not           = $this->checkMagicWhereNot($operator, $not);
        }

        return [$finalOperator, $position, $not];

        // This is not working at the moment
        // todo riddles - fix this
        //if ($finalOperator == 'many') {
        //    $where = null;
        //    foreach ($args[0] as $column => $value) {
        //        $where = $this->getWhere(
        //            $column,            // Column
        //            $finalOperator,    // Operator
        //            $value,             // Value
        //            $not,               // Inverse
        //            $position            // First or last
        //        );
        //    }
        //
        //    return $where;
        //}
    }

    private function checkMagicWhereFinalOperator($operator, $finalOperator)
    {
        if (in_array($operator, ['in', 'between', 'like', 'null', '='])) {
            return $operator;
        }

        return $finalOperator;
    }

    private function checkMagicWherePosition($operator, $position)
    {
        if (in_array($operator, ['first', 'last'])) {
            return $operator;
        }

        return $position;
    }

    private function checkMagicWhereNot($operator, $not)
    {
        if (in_array($operator, ['not'])) {
            return true;
        }

        return $not;
    }

    /**
     * Search a collection for the value specified.
     *
     * @param  string  $column   The column to search.
     * @param  string  $operator The operation to use during search.
     * @param  mixed   $value    The value to search for.
     * @param  boolean $inverse  Invert the results.
     * @param  string  $position Return the first or last object in the collection.
     *
     * @return self                 Return the filtered collection.
     */
    protected function getWhere($column, $operator, $value = null, $inverse = false, $position = null)
    {
        $output = clone $this;
        foreach ($output->items as $key => $item) {
            if (strstr($column, '->')) {
                $forget = $this->handleMultiTap($item, $column, $value, $operator, $inverse);
            } else {
                // No tap direct object access
                $forget = $this->whereObject($item, $column, $operator, $value, $inverse);
            }

            if ($forget == true) {
                $output->forget($key);
                continue;
            }
        }

        // Handel first and last.
        if (! is_null($position)) {
            return $output->$position();
        }

        return $output;
    }

    /**
     * Compare the object and column passed with the value using the operator
     *
     * @param  object  $object   The object we are searching.
     * @param  string  $column   The column to compare.
     * @param  string  $operator What type of comparison operation to perform.
     * @param  mixed   $value    The value to search for.
     * @param  boolean $inverse  Invert the results.
     *
     * @return boolean              Return true if the object should be removed from the collection.
     */
    private function whereObject($object, $column, $operator, $value = null, $inverse = false)
    {
        // Remove the object is the column does not exits.
        // Only do this if we aren't looking for null
        if (! $object->$column && $operator != 'null') {
            return true;
        }

        $method = 'getWhere' . ucfirst($operator);

        if (method_exists($this, $method)) {
            return $this->{$method}($object, $column, $value, $inverse);
        }

        return $this->getWhereDefault($object, $column, $value, $inverse);
    }

    private function getWhereIn($object, $column, $value, $inverse)
    {
        if (! in_array($object->$column, $value) && $inverse == false) {
            return true;
        }
        if (in_array($object->$column, $value) && $inverse == true) {
            return true;
        }

        return false;
    }

    private function getWhereBetween($object, $column, $value, $inverse)
    {
        if ($inverse == false) {
            if ($object->$column < $value[0] || $object->$column > $value[1]) {
                return true;
            }
        } else {
            if ($object->$column >= $value[0] && $object->$column <= $value[1]) {
                return true;
            }
        }

        return false;
    }

    private function getWhereLike($object, $column, $value, $inverse)
    {
        if (! strstr($object->$column, $value) && $inverse == false) {
            return true;
        }
        if (strstr($object->$column, $value) && $inverse == true) {
            return true;
        }

        return false;
    }

    private function getWhereNull($object, $column, $value, $inverse)
    {
        if ((! is_null($object->$column) || $object->$column != null) && $inverse == false) {
            return true;
        }
        if ((is_null($object->$column) || $object->$column == null) && $inverse == true) {
            return true;
        }

        return false;
    }

    private function getWhereDefault($object, $column, $value, $inverse)
    {
        if ($object->$column != $value && $inverse == false) {
            return true;
        }
        if ($object->$column == $value && $inverse == true) {
            return true;
        }

        return false;
    }
}
