<?php

namespace WireClippers\EntityModule\Handler;

use WireClippers\BuilderModule\ClassInterface;
use WireClippers\EntityModule\CollectionClass;
use WireClippers\EntityModule\Command;
use WireClippers\EntityModule\Entity;
use WireClippers\CaseConverter;
use WireClippers\Context;

class CollectionHandler
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
        $alias = $command->getName() . '-collection';
        $current = $classes->getByAlias($alias) ?? $classes->getByName($alias);
        if ($current) {
            throw new \LogicException("You cannot modify '$alias'.");
        }

        $parameters = [];
        foreach ($command->getParameters() as $parameter) {
            [$parameterName, $parameterValue] = array_pad(explode(':', $parameter), 2, null);
            $parameters[$parameterName] = $parameterValue;
        }
        if (empty($parameters['type'])) {
            throw new \LogicException("Parameter 'type' is required and should be a valid alias or existing class name.");
        }

        $type = $parameters['type'];
        /** @var Entity $class */
        $class = $classes->getByAlias($type) ?? $classes->getByName($type);
        if (!$class) {
            throw new \LogicException("Cannot create collection of '$type'. There is no alias/class '$type'.");
        }

        $classes[$alias] = new CollectionClass($this->caseConverter->pascalize($alias), $class->name());
    }

    public static function getName():string{
        return 'collection';
    }

}