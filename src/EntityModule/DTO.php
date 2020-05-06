<?php


namespace WireClippers\EntityModule;

use Nette\PhpGenerator\Property;
use WireClippers\EntityModule\Entity\Field;
use WireClippers\EntityModule\Entity\Method;

class DTO extends ClassInterface
{
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
        $class = $this->type;

//        if (!$class->hasMethod(self::CONSTRUCT_METHOD)) {
//            $class->addMethod(self::CONSTRUCT_METHOD);
//        }
//        $constructor = $class->getMethod(self::CONSTRUCT_METHOD);
//        $constructor
//            ->addBody("\$this->{$name} = \${$name};")
//            ->addParameter($name)
//            ->setType($type);

        $property = $class
            ->addProperty($field->name())
            ->setPublic();
//        if (in_array(self::PROPERTY_TYPES, $settings, true)) {
        /* @fixme add as a 7.4 flag --property-types */
        $property->setType($field->type());
//        }

//        $class
//            ->addMethod($name)
//            ->setBody("return \$this->$name;")
//            ->setPublic()
//            ->setReturnType($type);
        return $this;
    }

    public function addMethod(Method $method)
    {
        throw new \LogicException('DTO should not contain any additional methods.');
    }
}