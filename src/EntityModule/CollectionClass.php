<?php


namespace WireClippers\EntityModule;

use InvalidArgumentException;
use Nette\PhpGenerator\Parameter;
use WireClippers\BuilderModule\ClassInterface;
use WireClippers\EntityModule\Entity\Method;

class CollectionClass extends ClassInterface
{
    public function __construct(string $name, string $type)
    {
        parent::__construct($name);
        $this->type
            ->setClass()
            ->addExtend($this->parentClassName());

        $this
            ->type
            ->addMethod('offsetSet')
            ->setBody("if(!\$value instanceof $type)\n" .
                "    throw new " . InvalidArgumentException::class . "(__CLASS__.' cannot accept instances of '. get_class(\$value));\n" .
                'parent::offsetSet($index, $value);')
            ->setParameters([new Parameter('index'), new Parameter('value')]);

        $this
            ->type
            ->addMethod('append')
            ->setBody("if(!\$value instanceof $type)\n" .
                "    throw new " . InvalidArgumentException::class . "(__CLASS__.' cannot accept instances of '. get_class(\$value));\n" .
                'parent::append($value);')
            ->setParameters([new Parameter('value')]);

        $this
            ->type
            ->addMethod('__construct')
            ->setBody(
                "foreach(\$input as \$value){\n".
                "    if(!\$value instanceof $type) {\n" .
                "        throw new " . InvalidArgumentException::class . "(__CLASS__.' cannot accept instances of '. get_class(\$value));\n" .
                "    }\n".
                "}\n".
                'parent::__construct($input, $flags, $iterator_class);')
            ->setParameters([
                (new Parameter('input'))->setDefaultValue([]),
                (new Parameter('flags'))->setDefaultValue(0),
                (new Parameter('iterator_class'))->setDefaultValue(\ArrayIterator::class),
            ]);

        $this
            ->type
            ->addMethod('exchangeArray')
            ->setBody(
                "foreach(\$input as \$value){\n".
                "   if(!\$value instanceof $type) {\n" .
                '       throw new ' . InvalidArgumentException::class . "(__CLASS__.' cannot accept instances of '. get_class(\$value));\n" .
                "   }\n".
                "}\n".
                'parent::exchangeArray($input);')
            ->setParameters([
                (new Parameter('input'))->setDefaultValue([]),
            ]);
    }

    public function addMethod(Method $method)
    {
        throw new \BadMethodCallException('You cannot add methods to collection. At least now.');
    }

    public function extendsClass(string $name)
    {
        throw new \BadMethodCallException('Collection cannot extent anything except ' . $this->parentClassName());
    }

    private function parentClassName(): string
    {
        return \ArrayObject::class;
    }


}