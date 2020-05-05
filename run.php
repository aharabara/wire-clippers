<?php

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Printer;
use WireClippers\BuilderModule\AutoCompleteHandler;
use WireClippers\Collection\ClassCollection;
use WireClippers\Collection\ClassesCollection;
use WireClippers\Context;
use WireClippers\Parser;

require 'vendor/autoload.php';

print "`.`            - new class\n" .
    "`#`            - new interface\n" .
    "`>`            - extends class / implements interface\n" .
    "`[...members]` - class members/interface methods\n" .
    "GoodLuck!\n" .
    "===================\n";

$parser = new Parser();

$context = new Context(new ClassesCollection(), new ClassCollection());
readline_completion_function(new AutoCompleteHandler($context));


while ($line = readline('wrc>>')) {
    if (empty($line)) {
        break;
    }
    try {
        $parser->run($line . "\n", $context);
    } catch (Throwable $e) {
        $output = "Error : {$e->getMessage()}\n";
        if (Parser::DEBUG) {
            $output .= "{$e->getFile()}:{$e->getLine()}\n";
        }
        print $output;
    }
}

$printer = new Printer();
$output = '';
foreach ($context->interfaces() as $interfaces) {
    $output .= $printer->printClass($interfaces->getClassType());
}
foreach ($context->classes() as $class) {
    $output .= $printer->printClass($class->getClassType());
}
file_put_contents('./domain.php', "<?php\n\n" . $output);



/*
 *  .proposal[ name:string, createdBy:( .user[firstName:string, lastName:string, email:(.email[value:string])] ), updatedBy:(.user), createdAt:DateTimeImmutable, updatedAt:DateTimeImmutable, sentBy:(.user), billTo:( .billing_details[ user:(.user), address:( .address[ country:(.country[name:string]), state:(.state[country:(.country), name:string]) ] ) ] ) ]
 *  - billing_details are not replaced
 *  - .proposal is not present after parsing
 *  - hard to debug - introduce pausing with context dump (psyshell)
 *
 * */
