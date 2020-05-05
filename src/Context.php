<?php

namespace WireClippers;

use WireClippers\BuilderModule\ClassInterface;
use WireClippers\BuilderModule\Entity;
use WireClippers\Collection\ClassCollection;
use WireClippers\Collection\ClassesCollection;

class Context
{
    /** @var ClassesCollection */
    private $classes;
    /** @var ClassCollection */
    private $interfaces;

    /**
     * Context constructor.
     * @param ClassesCollection $classes
     * @param ClassCollection $interfaces
     */
    public function __construct(ClassesCollection $classes, ClassCollection $interfaces)
    {
        $this->classes = $classes;
        $this->interfaces = $interfaces;
    }

    /**
     * @return ClassesCollection|Entity[]
     */
    public function classes(): ClassesCollection
    {
        return $this->classes;
    }

    /**
     * @return ClassCollection
     */
    public function interfaces(): ClassCollection
    {
        return $this->interfaces;
    }
}