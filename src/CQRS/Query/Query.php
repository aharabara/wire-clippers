<?php

namespace WireClippers\CQRS\Query;

class Query
{
    private $name;

    private $usage;


    public function __construct(string $name, string $usage)
    {
        $this->name = $name;
        $this->usage = $usage;
    }


    public function name(): string
    {
        return $this->name;
    }


    public function usage(): string
    {
        return $this->usage;
    }
}
