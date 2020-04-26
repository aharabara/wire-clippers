<?php

namespace WireClippers;

use WireClippers\Collection\ClassTypeCollection;

class Context
{
    private ClassTypeCollection $classes;
    private ClassTypeCollection $interfaces;
    private ?Item $current;

    /**
     * Context constructor.
     * @param ClassTypeCollection $classes
     * @param ClassTypeCollection $interfaces
     */
    public function __construct(ClassTypeCollection $classes, ClassTypeCollection $interfaces)
    {
        $this->classes = $classes;
        $this->interfaces = $interfaces;
        $this->current = null;
    }

    /**
     * @return ClassTypeCollection
     */
    public function classes(): ClassTypeCollection
    {
        return $this->classes;
    }

    /**
     * @return ClassTypeCollection
     */
    public function interfaces(): ClassTypeCollection
    {
        return $this->interfaces;
    }

    /**
     * @return Item
     */
    public function current(): Item
    {
        return $this->current;
    }

    /**
     * @param Item $item
     */
    public function setCurrent(Item $item): void
    {
        $this->current = $item;
    }
}