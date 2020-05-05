<?php

namespace WireClippers\BuilderModule;

use Nette\PhpGenerator\ClassType;
use WireClippers\EntityModule\Entity\Method;

class ClassInterface
{
    const KEYWORDS = ['use', 'class', 'interface', 'switch', 'list', 'namespace'];
    /**
     * @var ClassType
     */
    protected $type;

    public function __construct(string $name)
    {
        if (in_array(strtolower($name), self::KEYWORDS)){
            throw new \LogicException(sprintf('You cannot create instance of %s with name %s', get_class($this), $name));
        }
        $this->type = new ClassType($name);
        $this->type->setInterface();
    }

    public function name(): string
    {
        return $this->type->getName();
    }


    public function addMethod(Method $method)
    {
        $this->type
            ->addMethod($method->name())
            ->setPublic()
//            ->setReturnType($type)
        //  ->setSignature(Parameter ...$parameters see Field::class)
        ;
        return $this;
    }

    public function extendsClass(string $name)
    {
        $this->type->addExtend($name);
        return $this;
    }

    /**
     * @return ClassType
     */
    public function getClassType(): ClassType
    {
        return $this->type;
    }
}