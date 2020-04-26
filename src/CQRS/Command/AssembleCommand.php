<?php

namespace WireClippers\CQRS\Command;

class AssembleCommand extends Command
{
    private $namespace;

    public function __construct(string $name, string $usage, string $namespace)
    {
        $this->namespace = $namespace;
        parent::__construct($name, $usage);
    }


    public function namespace(): string
    {
        return $this->namespace;
    }
}