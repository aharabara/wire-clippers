<?php


use PHPUnit\Framework\TestCase;
use WireClippers\Context;
use WireClippers\EntityModule\Collection\ClassCollection;
use WireClippers\Parser;

class EntityHandlerTest extends TestCase
{

    public function basicClassProvider()
    {
        return [
            ['user@entity[]', ['User' => []]],
            [
                'user@entity[email:string,name:string,age:int]',
                ['User' => ['email' => 'string', 'name' => 'string', 'age' => 'int']]
            ],
            [
                'user@entity[email:Email]', /*@fixme add check for class existence and alias/class resolving for types */
                [
                    'User' => ['email'=>'Email'],
                ]
            ],
        ];
    }

    /**
     * @dataProvider basicClassProvider
     * @param string $code
     * @param array $expected
     */
    public function testBasicClass(string $code, array $expected)
    {
        /** @var \Nette\PhpGenerator\ClassType[]|ArrayObject $classes */
        $context = new Context(new ClassCollection());
        (new Parser([Parser::PROPERTY_TYPES]))->run($code, $context);
        foreach ($context->classes() as $alias => $entity) {
            $className = $entity->name();
            self::assertArrayHasKey($className, $expected);
            foreach ($entity->fields() as $field) {
                self::assertArrayHasKey($field->name(), $expected[$className]);
                self::assertEquals($expected[$className][$field->name()], $field->type());
            }
        }
    }

}
