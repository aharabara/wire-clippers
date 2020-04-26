<?php

namespace WireClippers\CQRS\Command;

class BuildCommand extends Command
{
    private $code;


    public function __construct(string $name, string $usage, string $code)
    {
        $this->code = $code;
        parent::__construct($name, $usage);
    }


    public function code(): string
    {
        return $this->code;
    }
}
