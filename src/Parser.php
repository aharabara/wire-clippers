<?php

namespace WireClippers;


use ArrayObject;
use BadMethodCallException;
use InvalidArgumentException;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Parameter;

class Parser
{

    const DEBUG = true;
    const CONSTRUCT_METHOD = '__construct';

    const CLASS_CONTEXT = '.';
    const MEMBER_CONEXT_START = '[';
    const MEMBER_CONTEXT_END = ']';
    const SUB_CONTEXT_START = '(';
    const SUB_CONTEXT_END = ')';
    const INTERFACE_CONTEXT = '#';
    const CONTEXT_EXTENDS = '>';
    const CONTEXT_END = "\n";
    const OPERATIONS = [
        self::CLASS_CONTEXT, self::MEMBER_CONEXT_START, self::MEMBER_CONTEXT_END, self::INTERFACE_CONTEXT, self::CONTEXT_EXTENDS, self::CONTEXT_END
    ];

    public function run(string $code, ArrayObject $classes)
    {
        $code = str_replace(' ', '', $code);
        $this->validate($code);
        $this->parse($code, $classes);
    }

    /**
     * @param string $code
     * @param ArrayObject|ClassType[] $classes
     * @return Item|null
     */
    protected function parse(string $code, ArrayObject $classes): ?Item
    {
        print "->$code\n";
        if (empty($code)) {
            return null;
        }
        $code = $this->preparse($code, $classes);
        print "<-$code\n\n";
//    debug(" - PARSE: $code");
        $context = null;
        $symbols = str_split($code);
        while (!empty($symbols)) {
            switch ($symbol = array_shift($symbols)) {
                case self::CLASS_CONTEXT :
                    // new class context
                    $alias = $this->getToken($symbols);
                    /**fixme add alias normalization for cases like "user-collection" and "user_collection"*/
                    if (isset($classes[$alias])) {
                        $context = new Item($alias, $classes[$alias]);
                    } else {
                        $context = new Item($alias, new ClassType($this->toPascalCase($alias)));
                    }
//                debug(" - - new class $alias");
                    break;
                case self::INTERFACE_CONTEXT :
                    // new class context
                    $alias = $this->getToken($symbols);
                    if (isset($classes[$alias])) {
                        $context = new Item($alias, $classes[$alias]);
                    } else {
                        $context = new Item(
                            $alias,
                            (new ClassType($this->toPascalCase($alias) . 'Interface'))->setInterface()
                        );
                    }
//                debug(" - - new interface $alias");
                    break;
                case self::CONTEXT_EXTENDS :
                    /** @fixme cannot resolve replacements like ".user">"User". Add some shit like searchClass, searchInterface and searchByAlias */
                    // context extends class/interface
                    $alias = $this->getToken($symbols);
                    $extendName = $this->toPascalCase($alias);
                    $classType = $context->getClass();
                    $type = $classes[$alias] ?? null;
                    if (!$type) {
                        foreach ($classes as $class) {
                            if ($class->getName() === $extendName) {
                                $type = $class;
                                break;
                            }
                        }
                    }
                    if ($type) {
                        if ($type->isInterface()) {
                            $classType->addImplement($type->getName());
                        } else {
                            $classType->addExtend($type->getName());
                            if ($classType->hasMethod(self::CONSTRUCT_METHOD)) {
                                $method = $classType->getMethod(self::CONSTRUCT_METHOD);
                                $parameters = $method->getParameters();
                                $parentParamsStr = '';
                                if ($type->hasMethod(self::CONSTRUCT_METHOD)) {
                                    $parentParams = $type->getMethod(self::CONSTRUCT_METHOD)->getParameters();
                                    $parameters = array_merge($parentParams, $parameters);
                                    $parentParamsStr = implode(',', array_map(static function (Parameter $parameter) {
                                        return "\${$parameter->getName()}";
                                    }, $parentParams));
                                }
                                $method
                                    ->setParameters(array_merge($parameters))
                                    ->addBody(sprintf('parent::%s(%s);', self::CONSTRUCT_METHOD, $parentParamsStr));
                                /** add parent class params first + add them to parent call*/
                            }
                        }
                        break;
                    }
                    if (interface_exists($extendName)) {
                        $classType->addImplement($extendName);
                    } elseif (class_exists($extendName)) {
                        $classType->addExtend($extendName);
                    } else {
                        throw new InvalidArgumentException("There is no class/interface named '$extendName'");
                    }
                    break;
                case self::MEMBER_CONEXT_START :
                    // start context member adding (field or method)
                    $members = $this->getToken($symbols);
                    foreach (explode(',', $members) as $member) {
                        [$member, $type] = array_pad(explode(':', $member), 2, null);
                        $member = $this->toCamelCase(trim($member));
                        $class = $context->getClass();
                        if ($class->isInterface()) {
                            $class
                                ->addMethod($member)
                                ->setPublic()
                                ->setReturnType($type);
                        } else /*is class */ {
                            if (!$class->hasMethod(self::CONSTRUCT_METHOD)) {
                                $class->addMethod(self::CONSTRUCT_METHOD);
                            }
                            $constructor = $class->getMethod(self::CONSTRUCT_METHOD);
                            $constructor
                                ->addBody("\$this->{$member} = \${$member};")
                                ->addParameter($member)
                                ->setType($type);

                            $class
                                ->addProperty($member)
                                ->setPrivate();
//                                ->setType($type); @fixme add as a 7.4 flag --property-types

                            $class
                                ->addMethod($this->toCamelCase($member))
                                ->setBody("return \$this->$member;")
                                ->setReturnType($type);
                        }
                    }
                    break;
                case self::MEMBER_CONTEXT_END :
                    // end context member adding (field or method)
                    break;
            }
        }
//    debug("PARSED: $code");
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
        $stopSymbols = array_merge(self::OPERATIONS, [null]);
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
        return ucfirst(str_replace(['_', '-'], '', ucwords($string, '_-')));
    }

    /**
     * @param string $code
     * @param ArrayObject $context
     * @return mixed
     */
    function preparse(string $code, ArrayObject $context)
    {
        $from = $code;
//    debug("PREPARSE: $code");
        $re = '/\((?:[^)(]+|(?R))*+\)/';
        do {
            preg_match_all($re, $code, $matches, PREG_SET_ORDER, 0);
            $matches = array_filter(array_column($matches, 0));
            $line = array_pop($matches);
            $line = trim($line, '()');
//        readline(); # for debug
            if (empty($line)) {
                break;
            }
            $item = $this->parse($line, $context);
            $code = str_replace("($line)", $item->getClass()->getName(), $code);
        } while (!empty($matches));
//        if (strpos($line, '(') !== FALSE) {
//            $subcode = preparse($line, $context);
//            $code = str_replace($line, $subcode, $code);
//            $line = $subcode;
//        }
//    }
//    debug("PREPARSED: $from TO $code\n");
        return $code;
    }

    function debug(string $print)
    {
        if (self::DEBUG) {
            print $print . "\n";
        }
    }

    private function validate(string $code): void
    {
        $bracketPairs = [['(', ')',], ['[', ']'], ['{', '}']];
        foreach ($bracketPairs as [$open, $close]) {
            if (substr_count($code, $open) === substr_count($code, $close)) {
                continue;
            }
            if (substr_count($code, $open) < substr_count($code, $close)) {
                throw new BadMethodCallException("You miss one '$open' symbol in '$code'.");
            }
            throw new BadMethodCallException("You miss one '$close' symbol in '$code'.");

        }
    }

    /**
     * @param string $string
     * @return string
     */
    private function toCamelCase(string $string): string
    {
        return lcfirst($this->toPascalCase($string));
    }
}