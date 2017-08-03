<?php

namespace JumpGate\Database\Searching\Contracts;

interface Searchable
{
    public function getSearchProvider();

    public function getSearchParameters();

    public function search($parameters);
}
