<?php

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Printer;

require 'vendor/autoload.php';

print "`.`            - new class\n" .
    "`#`            - new interface\n" .
    "`>`            - extends class / implements interface\n" .
    "`[...members]` - class members/interface methods\n" .
    "GoodLuck!\n" .
    "===================\n";

$context = new ArrayObject();
$parser = new \WireClippers\Parser();

readline_completion_function(function ($_input, $start, $end) use ($context) {
    $defaultTypes = ['string', 'int', 'bool', 'float', \ArrayObject::class];
    $input = readline_info('line_buffer');
    /* @todo add basic types like int, string and etc, */
    /* @todo add command autocomplete (this handler is only for build and preview commands) */
    $lastSymbol = substr($input, strlen($input) - 1, 1);
    $primary = $aliases = array_keys($context->getArrayCopy());
    $secondary = $classes = array_map(static function (ClassType $type) {
        return $type->getName();
    }, $context->getArrayCopy());
    if ($lastSymbol === ':') {
        $format = "{$input}%s";
        $primary = array_merge($defaultTypes, $classes);
    } elseif ($lastSymbol === '(') {
        $format = "{$input}.%s)";
    } elseif ('.' === $lastSymbol) {
        $format = "{$input}%s";
        $primary = $aliases;
    } elseif ('>' === $lastSymbol) {
        $format = "{$input}%s";
        $primary = $aliases;
    } elseif (in_array($lastSymbol, ['[', ','])) {
        $format = "{$input}%s:%s";
    } else {
        $format = "$input.%s";
    }
    return array_map(static function (?string $first = null, ?string $second = null) use ($format) {
        return sprintf($format, $first, $second);
    }, $primary, $secondary);
});


while ($line = readline('wrc>>')) {
    if (empty($line)) {
        break;
    }
    $parser->run($line . "\n", $context);
}

$printer = new Printer();
$output = '';
foreach ($context as $class) {
    $output .= $printer->printClass($class);
}
file_put_contents('./domain.php', "<?php\n\n" . $output);



/*
 *  .proposal[ name:string, createdBy:( .user[firstName:string, lastName:string, email:(.email[value:string])] ), updatedBy:(.user), createdAt:DateTimeImmutable, updatedAt:DateTimeImmutable, sentBy:(.user), billTo:( .billing_details[ user:(.user), address:( .address[ country:(.country[name:string]), state:(.state[country:(.country), name:string]) ] ) ] ) ]
 *  - billing_details are not replaced
 *  - .proposal is not present after parsing
 *  - hard to debug - introduce pausing with context dump (psyshell)
 *
 * */
