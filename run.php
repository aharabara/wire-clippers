<?php

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Printer;
use WireClipper\Item;

require './vendor/autoload.php';

const CLASS_CONTEXT = '.';
const MEMBER_CONEXT_START = '[';
const MEMBER_CONTEXT_END = ']';
const SUB_CONTEXT_START = '(';
const SUB_CONTEXT_END = ')';
const INTERFACE_CONTEXT = '#';
const CONTEXT_EXTENDS = '>';
const CONTEXT_END = "\n";
const OPERATIONS = [
    CLASS_CONTEXT, MEMBER_CONEXT_START, MEMBER_CONTEXT_END, INTERFACE_CONTEXT, CONTEXT_EXTENDS, CONTEXT_END
];

/**
 * @param string $code
 * @param ArrayObject|ClassType[] $classes
 * @return Item
 */
function parse(string $code, ArrayObject $classes)
{
    $context = null;
    $symbols = str_split($code);
    while (!empty($symbols)) {
        switch ($symbol = array_shift($symbols)) {
            case CLASS_CONTEXT :
                // new class context
                $alias = getToken($symbols);
                $className = toPascalCase($alias);
                $class = new Nette\PhpGenerator\ClassType($className);
                $context = new Item($alias, $class);
                break;
            case INTERFACE_CONTEXT :
                // new class context
                $alias = getToken($symbols);
                $className = toPascalCase($alias);
                $interface = new Nette\PhpGenerator\ClassType($className . 'Interface');
                $interface->setInterface();
                $context = new Item($alias, $interface);
                break;
            case CONTEXT_EXTENDS :
                // context extends class/interface
                $alias = getToken($symbols);
                $extendName = toPascalCase($alias);
                if (isset($classes[$alias])) {
                    $extendName = $classes[$alias]->getName();
                }
                if (interface_exists($extendName)) {
                    $context->getClass()->addImplement($extendName);
                } elseif (isset($classes[$alias]) || class_exists($extendName)) {
                    $context->getClass()->addExtend($extendName);
                } else {
                    throw new InvalidArgumentException("There is no class/interface named '$extendName'");
                }
                break;
            case MEMBER_CONEXT_START :
                // start context member adding (field or method)
                $members = getToken($symbols);
                foreach (explode(',', $members) as $member) {
                    [$member, $type] = array_pad(explode(":", $member), 2, null);
                    $member = trim($member);
                    if ($context->isInterface()) {
                        $context->getClass()
                            ->addMethod($member)
                            ->setPublic()
                            ->setReturnType($type);
                    } else /*is class */ {
                        $context->getClass()
                            ->addProperty($member)
                            ->setPrivate()
                            ->setType($type);

                        $context->getClass()
                            ->addMethod('get' . toPascalCase($member))
                            ->setBody("return \$this->$member;")
                            ->setReturnType($type);
                    }
                }
                break;
            case MEMBER_CONTEXT_END :
                // end context member adding (field or method)
                break;
            case CONTEXT_END:
                // close context
                $printer = new Printer();
                foreach ($classes as $class) {
                    echo $printer->printClass($class);
                }
                break;
        }
    }
    $classes[$context->getAlias()] = $context->getClass();
    return $context;
}

/**
 * @param array $symbols
 * @return string
 */
function getToken(array $symbols): string
{
    $className = '';
    $stopSymbols = array_merge(OPERATIONS, [null]);
    while (!in_array($symbol = array_shift($symbols), $stopSymbols, true)) {
        $className .= $symbol;
    }
    return $className;
}

function toPascalCase(string $string, array $dontStrip = [])
{
    /*
     * This will take any dash or underscore turn it into a space, run ucwords against
     * it so it capitalizes the first letter in all words separated by a space then it
     * turns and deletes all spaces.
     */
    return ucfirst(str_replace(' ', '', ucwords(preg_replace('/^a-z0-9' . implode('', $dontStrip) . ']+/', ' ', $string))));
}

print "`.`            - new class\n" .
    "`#`            - new interface\n" .
    "`>`            - extends class / implements interface\n" .
    "`[...members]` - class members/interface methods\n" .
    "GoodLuck!\n" .
    "===================\n";

$context = new ArrayObject();

$str = ".user[name:string, email:(.email[address:string]), password:(.password[value:string])]";

/**
 * @param string $code
 * @param ArrayObject $context
 * @return mixed
 */
function preparse(string $code, ArrayObject $context)
{
    $re = '/\((?:[^)(]+|(?R))*+\)/';
    preg_match_all($re, $code, $matches, PREG_SET_ORDER, 0);
    $matches = array_column($matches, 0);
    foreach ($matches as $line) {
        $line = trim($line, '()');
        if (strpos($line, '(') !== FALSE) {
            print_r($line);
            preparse($line, $context);
        }
        $item = parse($line, $context);
        $code = str_replace($line, $item->getAlias(), $code);
        print_r([$line, $item->getAlias(), $code]);die;
        /** @fixme */
//        foreach ($context as $alias => $class){
//            $code = str_replace($line, $alias, $code);
//        }
//        print $code."\n";
    }
    return $matches;
}

//$matches = preparse($str, $context);
//die();


while ($line = readline('clippers>>')) {
    if (empty($line)) {
        break;
    }
    parse($line . "\n", $context);
}

