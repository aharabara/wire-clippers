<?php

namespace WireClippers\EntityModule\Handler;

use WireClippers\EntityModule\DTO;
use WireClippers\EntityModule\Entity;
use WireClippers\CaseConverter;
use WireClippers\Context;
use WireClippers\EntityModule\Command;
use WireClippers\EntityModule\Enum;

class DtoHandler
{
    /**
     * @var CaseConverter
     */
    private $caseConverter;

    public function __construct()
    {
        $this->caseConverter = new CaseConverter();
    }

    public function handle(Command $command, Context $context): void
    {
        $classes = $context->classes();

        $parameters = [];
        foreach ($command->getParameters() as $parameter){
            [$parameterName, $parameterValue] = array_pad(explode(':', $parameter), 2, null);
            $parameters[$parameterName] = $parameterValue;
        }
        if (empty($parameters['from'])){
            throw new \LogicException("Parameter 'from' is required and should be a valid alias or existing class name.");
        }

        $from = $parameters['from'];
        /** @var Entity $class */
        $class = $classes->getByAlias($from) ?? $classes->getByName($from);
        if (!$class) {
            throw new \LogicException("Cannot create DTO out of nothing. There is no alias '$from'.");
        }

        if (!$class instanceof Entity && !$class instanceof Enum){
            throw new \LogicException(
                sprintf(
                    "You cannot use '%s' with @%s, because '%s' is instance of %s",
                    $command->getName(), self::getName(), $command->getName(),
                    get_class($class)
                )
            );
        }


        $dtoAlias = $command->getName().'-dto';
        $dtoClassName = $this->caseConverter->pascalize($dtoAlias);
        $dto = $classes->getByAlias($dtoClassName);
        if (null === $dto){
            $dto = $classes[$dtoAlias] = new DTO($dtoClassName);
        }
        foreach ($class->fields() as $field) {
            $dto->addField($field);
        }
        $classes[$dtoAlias] = $dto;
    }

    public static function getName():string{
        return 'dto';
    }

}