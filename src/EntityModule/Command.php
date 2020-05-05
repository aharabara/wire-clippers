<?php


namespace WireClippers\EntityModule;


class Command
{
    private $name;
    private $parameters;

    public function __construct(string $name, array $members)
    {
        $this->name = $name;
        $this->parameters = $members;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}