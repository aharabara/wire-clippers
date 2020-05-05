<?php

namespace WireClippers\EntityModule\Entity;

class Method
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
//        $this->type = $type;
    }

    public function name():string{
        return $this->name;
    }
}