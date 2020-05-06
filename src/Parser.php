<?php

namespace WireClippers;


use BadMethodCallException;
use LogicException;
use WireClippers\BuilderModule\ClassInterface;
use WireClippers\EntityModule\Command;
use WireClippers\EntityModule\Handler\CollectionHandler;
use WireClippers\EntityModule\Handler\DtoHandler;
use WireClippers\EntityModule\Handler\EntityHandler;
use WireClippers\EntityModule\Handler\EnumHandler;
use WireClippers\EntityModule\Handler\InterfaceHandler;

class Parser
{

    const DEBUG = true;

    const CLASS_CONTEXT = '.';
    const MEMBER_CONEXT_START = '[';
    const MEMBER_CONTEXT_END = ']';
    const INTERFACE_CONTEXT = '#';
    const CONTEXT_EXTENDS = '>';
    const CONTEXT_END = "\n";
    const OPERATIONS = [
        self::CLASS_CONTEXT, self::MEMBER_CONEXT_START, self::MEMBER_CONTEXT_END, self::INTERFACE_CONTEXT, self::CONTEXT_EXTENDS, self::CONTEXT_END
    ];
    const PROPERTY_TYPES = 'settings.property-types';

    /** @var bool[] */
    private $settings;

    /** @var CaseConverter */
    private $snakeToPascalCaseConverter;
    private $handlers;

    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
        $this->handlers = [
            DtoHandler::getName() => new DtoHandler(),
            EntityHandler::getName() => new EntityHandler(),
            InterfaceHandler::getName() => new InterfaceHandler(),
            CollectionHandler::getName() => new CollectionHandler(),
            EnumHandler::getName() => new EnumHandler(),
        ];
        $this->snakeToPascalCaseConverter = new CaseConverter();
    }

    public function run(string $code, Context $context)
    {
        $code = str_replace(' ', '', $code);
        $this->validate($code);
        $this->parse($code, $context);
    }

    /**
     * @param string $code
     * @param Context $context
     * @return string
     */
    protected function parse(string $code, Context $context): ?string
    {
        /** @var Entity|ClassInterface|null $current */
        $current = null;
        $settings = $this->settings;

        preg_match_all('/(?<name>.+)@(?<command>.+)(?:\[)(?<members>.+)?(?:\])/', $code, $matches, PREG_SET_ORDER, 0);
        $matches = array_shift($matches);
        $name = $matches['name'] ?? null;
        $command = $matches['command'] ?? null;
        $members = $matches['members'] ?? [];

        if (!empty($members)) {
            preg_match_all('/(.+?)(?:,|$)/m', $members, $members, PREG_SET_ORDER, 0);
            $members = array_map('trim', array_column($members, 1));
        }
        $handler = $this->getCommandHanlerByCommandName($command);
        if (!$handler) {
            throw new LogicException("There is no command handler '$command'. You can create one using '<name>@wrc-command'");
        }
        $handler->handle(new Command($name, $members), $context);
        return null;

        /** @fixme add common class settings. ex: users@collection -> UserCollection>IlluminateCollection, or Users>ArrayCollection, */
        /** @fixme add settings for --property-type, --return-type, --short-getters, --setters */
        /** @fixme add assemble/disassemble commands that will collect all namespace classes will allow to transform them */

        print "->$code\n";
        if (empty($code)) {
            return null;
        }
        // @fixme I am getting rid of preparse, because in the context of readline we don't need recursive parsing.
        // @fixme we will keep it "proceduralish" way
//        $code = $this->preparse($code, $context);
        print "<-$code\n\n";
//    debug(" - PARSE: $code");
        $symbols = str_split($code);
        while (!empty($symbols)) {
            switch ($symbol = array_shift($symbols)) {
                case self::INTERFACE_CONTEXT :
                    // new class context
                    $alias = $this->getToken($symbols);
                    if ($classes->hasAlias($alias)) {
                        $current = $classes[$alias];
                    } else {
                        $current = $classes[$alias] = new ClassInterface($this->snakeToPascalCaseConverter->pascalize($alias) . 'Interface');
                    }
                    break;
                /*@fixme !!! replace `>` with ':', so we will have .user_collection:\ArrayObject */
                /*@fixme !!! Add custom class handlers (like entity or class-interface), so I could create collections/controlles and mappers
                    + user@entity[...methods] (supply signatures later as prompts?)
                    + user@dto[from:User]
                    + user@interface[...methods] (supply signatures later as prompts?)
                    + users@collection[type:User]
                    + users@enum[value1, value2, value3]
                    - user@transfromer[from:User, to:UserDTO]
                    - creat_user@cqrs-command[...fields]
                    - find_user@cqrs-query[...fields]
                    - user@cqrs-handler[for:<Command/Query>]
                    - encoder@chaining[json:JsonEncoder, yaml:YamlEncoder, xml:XmlEncoder]
                    - encryptor@chaining[md5:Md5Encrypter, sha256:Sha256Encrypter, xml:XmlEncoder]

                add postprocessors and implement a pipeline with these processors. fo example NamespaceDisassembleProcessor

                    @todo <name>@<handler>[parameters with autocomplete]
                    name@<command-autocomplete-handler>[command-parameter-autocomplete-handler
                 */
                /*@fixme !!! Remove extend by alias */
                case self::CONTEXT_EXTENDS :
                    // context extends class/interface
                    $alias = $this->getToken($symbols);
                    $extendName = $this->snakeToPascalCaseConverter->pascalize($alias);

                    if ($current === null) {
                        throw new LogicException('Nothing to extend.');
                    }

                    if (interface_exists($extendName) || class_exists($extendName)) {
                        $current->extendsClass($extendName);
                    } else {
                        $class = $interfaces->getByAlias($alias) ?? $interfaces->getByAlias($extendName);
                        if (null === $class) {
                            throw new LogicException(sprintf("'%s' does not exist.", $extendName));
                        }
                        $current->extendsClass($class->name());
                    }
                    break;
                case self::MEMBER_CONEXT_START :
                    // start context member adding (field or method)
                    $members = $this->getToken($symbols);

                    break;
                case self::MEMBER_CONTEXT_END :
                    // end context member adding (field or method)
                    break;
            }
        }
        return $current->name();
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


    /**
     * @param string $code
     * @param Context $context
     * @return mixed
     */
    function preparse(string $code, Context $context)
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
            $className = $this->parse($line, $context);
            $code = str_replace("($line)", $className, $code);
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
        $code = trim($code, " \n");
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

    private function getCommandHanlerByCommandName($command)
    {
        return $this->handlers[$command] ?? null;
    }


}