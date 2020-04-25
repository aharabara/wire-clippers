<?php

use Nette\PhpGenerator\Printer;

require 'vendor/autoload.php';

print "`.`            - new class\n" .
    "`#`            - new interface\n" .
    "`>`            - extends class / implements interface\n" .
    "`[...members]` - class members/interface methods\n" .
    "GoodLuck!\n" .
    "===================\n";

$context = new ArrayObject();
$parser = new \WireClipper\Parser();

while ($line = readline('clippers>>')) {
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
