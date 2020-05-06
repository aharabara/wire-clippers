<?php

namespace WireClippers\EntityModule\Handler;

use WireClippers\BuilderModule\ClassInterface;
use WireClippers\EntityModule\Command;
use WireClippers\EntityModule\Entity;
use WireClippers\CaseConverter;
use WireClippers\Context;

class InterfaceHandler
{
    /**
     * @var CaseConverter
     */
    private $caseConverter;

    public function __construct()
    {
        $this->caseConverter = new CaseConverter();
    }

    public function handle(Command $command, Context $context){
        $classes = $context->classes();
        $alias = $command->getName().'-interface';
        $current = $classes->getByAlias($alias) ?? $classes->getByName($alias);
        if (!$current) {
            $current = $classes[$alias] = new ClassInterface($this->caseConverter->pascalize($alias));
        }
        if (!$current instanceof ClassInterface){
            throw new \LogicException(
                sprintf(
                    "You cannot use '%s' with @%s, because '%s' is instance of %s",
                    $alias, self::getName(), $alias,
                    get_class($current)
                )
            );
        }
        foreach ($command->getParameters() as $member) {
            [$member, $type] = array_pad(explode(':', $member), 2, null);
            if (empty($member)) {
                continue;
            }
            $member = $this->caseConverter->camelize(trim($member));
            $current->addMethod(new Entity\Method($member));
//            if ($current instanceof ClassInterface) {
//                $current->addMethod($member, $type);
//            } elseif ($current instanceof Entity) {
//            } else {
//                throw new \LogicException(get_class($current) . ' not supported.');
//            }
        }
    }

    public static function getName():string{
        return 'interface';
    }

}