<?php

namespace PhpBench\Assertion\Ast;

use PhpBench\Util\TimeUnit;

class TimeValue implements Value
{
    /**
     * @var NumberNode
     */
    private $value;

    /**
     * @var string
     */
    private $unit;

    public function __construct(NumberNode $value, ?string $unit = TimeUnit::MICROSECONDS)
    {
        $this->value = $value;
        $this->unit = $unit;
    }

    public function unit(): string
    {
        return $this->unit;
    }

    public function value(): NumberNode
    {
        return $this->value;
    }

    public static function fromMicroseconds(int $int): self
    {
        return new self(new IntegerNode($int), TimeUnit::MICROSECONDS);
    }
}
