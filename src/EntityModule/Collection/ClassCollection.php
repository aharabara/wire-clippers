<?php

namespace WireClippers\EntityModule\Collection;

use WireClippers\EntityModule\ClassInterface;

class ClassCollection extends \ArrayObject
{
    /**
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->getArrayCopy());
    }

    /**
     * @return array
     */
    public function names(): array
    {
        return array_map(static function (ClassInterface $type) {
            return $type->name();
        }, $this->getArrayCopy());
    }

    /**
     * @param string $alias
     * @return bool
     */
    public function hasAlias(string $alias): bool
    {
        return isset($this[$alias]);
    }

    /**
     * @param string $alias
     * @return bool
     */
    public function hasName(string $alias): bool
    {
        return $this->getByName($alias) !== null;
    }

    /**
     * @param string $alias
     * @return ClassInterface|null
     */
    public function getByAlias(string $alias): ?ClassInterface
    {
        return $this[$alias] ?? null;
    }

    /**
     * @param string $className
     * @return ClassInterface|null
     */
    public function getByName(string $className): ?ClassInterface
    {
        foreach ($this as $class) {
            /** @var ClassInterface $class */
            if ($class->name() === $className) {
                return $class;
            }
        }
        return null;
    }

}
