<?php

namespace WireClippers\EntityModule\Handler;

use WireClippers\BuilderModule\ClassInterface;
use WireClippers\EntityModule\Command;
use WireClippers\EntityModule\Entity;
use WireClippers\CaseConverter;
use WireClippers\Context;
use WireClippers\EntityModule\Enum;

class EnumHandler
{
    /**
     * @var CaseConverter
     */
    private $caseConverter;

    public function __construct()
    {
        $this->caseConverter = new CaseConverter();
    }

    public function handle(Command $command, Context $context)
    {
        $classes = $context->classes();
        $alias = $command->getName().'-enum';
        $current = $classes->getByAlias($alias) ?? $classes->getByName($alias);
        if ($current instanceof Enum) {
            throw new \LogicException('You cannot change class of type enum.');
        }
        if ($current) {
            throw new \LogicException('You cannot use class ' . get_class($current) . ' as a enum class.');
        }
        $classes[$alias] = new Enum($this->caseConverter->pascalize($alias), ...$command->getParameters());
    }

    public static function getName(): string
    {
        return 'enum';
    }

}