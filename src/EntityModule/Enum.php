<?php

namespace WireClippers\EntityModule;

use Nette\PhpGenerator\Parameter;
use WireClippers\BuilderModule\ClassInterface;

class Enum extends ClassInterface
{
    public const ALLOWED_VALUES_CONSTANT_NAME = 'ALLOWED_VALUES';

    public function __construct(string $name, string ...$values)
    {
        parent::__construct($name);

        $this->type->setClass();
        $this->type->addConstant(self::ALLOWED_VALUES_CONSTANT_NAME, $values);
        $this->type->addProperty('value')->setType('string');
        /* @todo create a base class and copy main methods from it. you will have only to replace some things like class names
         */
        $this->type
            ->addMethod('__construct')
            ->setBody(
                'if(!in_array($value, self::' . self::ALLOWED_VALUES_CONSTANT_NAME . ")){\n" .
                '   throw new ' . \UnexpectedValueException::class .
                '(__CLASS__." cannot accept value \'$value\', because only [".implode(self::' . self::ALLOWED_VALUES_CONSTANT_NAME . ')."] are allowed"' .
                ");\n" .
                "}\n" .
                '$this->value = $value;')
            ->setParameters([
                (new Parameter('value'))->setType('string')
            ]);

        $this->type->addMethod('value')
            ->setReturnType('string')
//            ->setReturnNullable() fixme add a flag like nullable-enums?
            ->setBody('return $this->value;');
    }

    /**
     * @return array|string
     */
    public function values(): array
    {
        return $this->type->getConstants()[self::ALLOWED_VALUES_CONSTANT_NAME]->getValue();
    }

}