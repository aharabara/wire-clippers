<?php


use PHPUnit\Framework\TestCase;
use WireClipper\Parser;

class ParserTest extends TestCase
{

    public function basicClassProvider()
    {
        return [
            ['.user', ['User' => []]],
            [
                '.user[email:string, name:string, age:int]',
                ['User' => ['email' => 'string', 'name' => 'string', 'age' => 'int']]
            ],
            [
                '.user[email:(.email[value:string])]',
                [
                    'User' => ['email'=>'Email'],
                    'Email' => ['value' => 'string'],
                ]
            ],
            [
                '.user[' .
                    'name:string,'.
                    'address:(.address[' .
                        'country:(.country[name:string]),' .
                        'state:(.state[country:(.country), name:string]),' .
                        'city:(.city[state:(.state)])' .
                    '])' .
                ']',
                [
                    'User' => ['address'=>'Address', 'name' => 'string'],
                    'Address' => ['country' => 'Country', 'state' => 'State', 'city' => 'City'],
                    'Country' => ['name' => 'string'],
                    'State' => ['country' => 'Country', 'name' => 'string'],
                    'City' => ['state' => 'State', 'name' => 'string'],
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
        $classes = new ArrayObject();
        (new Parser())->run($code, $classes);
        $classes = $classes->getArrayCopy();
        foreach ($classes as $alias => $classType) {
            $className = $classType->getName();
            self::assertArrayHasKey($className, $expected);
            foreach ($classType->getProperties() as $property) {
                $propertyName = $property->getName();
                self::assertArrayHasKey($propertyName, $expected[$className]);
                self::assertEquals($expected[$className][$propertyName], $property->getType());
            }
        }
    }

//    public function propertyDefaultValueProvider()
//    {
//        return [
//            ['.user[email:string="somebodies@email.com", is_active:bool=true, age=29]', ['User' => [
//
//            ]]],
//            [
//                '.user[email:string, name:string, age:int]',
//                ['User' => ['email' => 'string', 'name' => 'string', 'age' => 'int']]
//            ],
//        ];
//    }
//

    public function classExtendingProvider(){
        return [];
    }
    public function testClassExtending()
    {
    }

    public function testInterfaceImplementing()
    {
    }

    public function testInterfaceExtending()
    {
    }

    public function testPropertyCorrectType()
    {
    }

    public function testPropertyCorrectGetter()
    {
    }

    public function testConstructorProperties()
    {
    }
}
