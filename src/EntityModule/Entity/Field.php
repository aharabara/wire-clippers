<?php

namespace WireClippers\EntityModule\Entity;

class Field
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $type;

    public function __construct(string $name, ?string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function name():string{
        return $this->name;
    }

    public function type():?string{
        return $this->type;
    }

}