<?php

namespace WireClippers\EntityModule\Autocomplete;

use ArrayObject;
use Nette\PhpGenerator\ClassType;
use WireClippers\Context;

class AutoCompleteHandler
{
    const DEFAULT_TYPES = ['string', 'int', 'bool', 'float', ArrayObject::class];
    /**
     * @var Context
     */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return array|null
     */
    public function __invoke(): ?array
    {
        $input = readline_info('line_buffer');
        /* @todo add command autocomplete (this handler is only for build and preview commands) */

        $lastSymbol = $this->strLastSymbol($input);
        $primary = $aliases = $this->context->classes()->keys();
        $secondary = $classes = $this->context->classes()->names();
        if ($lastSymbol === ':') {
            $format = "{$input}%s";
            $primary = array_merge(self::DEFAULT_TYPES, $classes);
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
    }

    /**
     * @param string $input
     * @return false|string
     */
    protected function strLastSymbol(string $input)
    {
        return $input[strlen($input) - 1];
    }

}