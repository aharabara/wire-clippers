<?php

namespace WireClippers;

use Nette\PhpGenerator\ClassType;

class Item
{
    /** @var string*/
    private $alias;

    /** @var ClassType */
    private $class;

    /**
     * Item constructor.
     * @param string $alias
     * @param ClassType $class
     */
    public function __construct(string $alias, ClassType $class)
    {
        $this->alias = $alias;
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @return ClassType
     */
    public function getClass(): ClassType
    {
        return $this->class;
    }

    /**
     * @param string $alias
     */
    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }

    /**
     * @param ClassType $class
     */
    public function setClass(ClassType $class): void
    {
        $this->class = $class;
    }
}
