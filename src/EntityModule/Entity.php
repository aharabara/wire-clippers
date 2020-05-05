<?php


namespace WireClippers\EntityModule;


use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Parameter;
use Nette\PhpGenerator\Property;
use WireClippers\BuilderModule\ClassInterface;
use WireClippers\EntityModule\Entity\Field;

class Entity extends ClassInterface
{
    const CONSTRUCT_METHOD = '__construct';

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->type->setClass();
    }

    /**
     * @return array|Field[]
     */
    public function fields(): array
    {
        $props = $this->type->getProperties();
        return array_map(static function (Property $prop): Field {
            return new Field($prop->getName(), $prop->getType());
        }, $props);
    }

    public function addField(Field $field)
    {
        $name = $field->name();
        $type = $field->type();
        $class = $this->type;

        if (!$class->hasMethod(self::CONSTRUCT_METHOD)) {
            $class->addMethod(self::CONSTRUCT_METHOD);
        }
        $constructor = $class->getMethod(self::CONSTRUCT_METHOD);
        $constructor
            ->addBody("\$this->{$name} = \${$name};")
            ->addParameter($name)
            ->setType($type);

        $property = $class
            ->addProperty($name)
            ->setPrivate();
//        if (in_array(self::PROPERTY_TYPES, $settings, true)) {
        /* @fixme add as a 7.4 flag --property-types */
        $property->setType($type);
//        }

        $class
            ->addMethod($name)
            ->setBody("return \$this->$name;")
            ->setPublic()
            ->setReturnType($type);
        return $this;
    }

    public function extendsClass(string $name){
        parent::extendsClass($name);
        if ($this->type->hasMethod(self::CONSTRUCT_METHOD)) {
            $method = $this->type->getMethod(self::CONSTRUCT_METHOD);
            $parameters = $method->getParameters();
            $parentParamsStr = '';
//            @fixme add parent::construct call
//            if ($this->type->hasMethod(self::CONSTRUCT_METHOD)) {
//                $parentParams = $this->type->getMethod(self::CONSTRUCT_METHOD)->getParameters();
//                $parameters = array_merge($parentParams, $parameters);
//                $parentParamsStr = implode(',', array_map(static function (Parameter $parameter) {
//                    return "\${$parameter->getName()}";
//                }, $parentParams));
//            }
//            $method
//                ->setParameters(array_merge($parameters))
//                ->addBody(sprintf('parent::%s(%s);', self::CONSTRUCT_METHOD, $parentParamsStr));
//            /** add parent class params first + add them to parent call*/
        }

        return $this;
    }

}