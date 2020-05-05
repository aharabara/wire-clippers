<?php


use PHPUnit\Framework\TestCase;
use WireClippers\Collection\ClassCollection;
use WireClippers\Collection\ClassesCollection;
use WireClippers\Context;
use WireClippers\Parser;

class DtoHandlerTest extends TestCase
{

    private /** @var Parser  */ $parser;

    protected function setUp(): void
    {
        /** @var \Nette\PhpGenerator\ClassType[]|ArrayObject $classes */
        $this->parser = new Parser([Parser::PROPERTY_TYPES]);

        parent::setUp();
    }

    public function dataProvider()
    {
        $entityName = 'user';
        return [
            [$entityName, "{$entityName}@entity[id:int, name:string, age:int]", "{$entityName}@dto[from:user]", ['id' => 'int', 'name' => 'string', 'age' => 'int']],
            [$entityName, "{$entityName}@entity[id:int, name:string]", "{$entityName}@dto[from:User]", ['id' => 'int', 'name' => 'string']],
            [$entityName, "{$entityName}@entity[id, name]", "{$entityName}@dto[from:User]", ['id' => null, 'name' => null]],
            [$entityName, "{$entityName}@entity[]", "{$entityName}@dto[from:User]", []],
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param string $name
     * @param string $entity
     * @param string $dto
     * @param array $fields
     */
    public function testDtoHandler(string $name, string $entity, string $dto, array $fields)
    {
        $context = new Context(new ClassesCollection(), new ClassCollection());
        $this->parser->run($entity, $context);
        $this->parser->run($dto, $context);
        /** @var \WireClippers\EntityModule\DTO $dto */
        $dto = $context->classes()->getByAlias($name);
        $dtoFields = [];
        foreach ($dto->fields() as $field) {
            $dtoFields[$field->name()] = $field->type();
        }
        self::assertSame($dtoFields, $fields);
    }
}
