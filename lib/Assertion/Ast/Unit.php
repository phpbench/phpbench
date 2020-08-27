<?php

namespace PhpBench\Assertion\Ast;

class Unit
{
    /**
     * @var string
     */
    private $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function type(): string
    {
        return $this->type;
    }

    public static function microseconds(): self
    {
        return new self('microseconds');
    }

    public static function percent(): self
    {
        return new self('%');
    }
}
