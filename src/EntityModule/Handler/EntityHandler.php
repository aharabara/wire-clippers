<?php

namespace WireClippers\EntityModule\Handler;

use WireClippers\BuilderModule\ClassInterface;
use WireClippers\EntityModule\Command;
use WireClippers\EntityModule\Entity;
use WireClippers\CaseConverter;
use WireClippers\Context;

class EntityHandler
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
        $alias = $command->getName();
        $current = $classes->getByAlias($alias) ?? $classes->getByName($alias);
        if (!$current) {
            $current = $classes[$alias] = new Entity($this->caseConverter->pascalize($alias));
        }

        if (!$current instanceof Entity){
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
            $current->addField(new Entity\Field($member, $type));
//            if ($current instanceof ClassInterface) {
//                $current->addMethod($member, $type);
//            } elseif ($current instanceof Entity) {
//            } else {
//                throw new \LogicException(get_class($current) . ' not supported.');
//            }
        }
    }

    public static function getName():string{
        return 'entity';
    }

}