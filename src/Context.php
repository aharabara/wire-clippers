<?php

namespace WireClippers;

use WireClippers\EntityModule\Collection\ClassCollection;

class Context
{
    /** @var ClassCollection */
    private $classes;

    /**
     * Context constructor.
     * @param ClassCollection $classes
     */
    public function __construct(ClassCollection $classes)
    {
        $this->classes = $classes;
    }

    /**
     * @return ClassCollection
     */
    public function classes(): ClassCollection
    {
        return $this->classes;
    }
}