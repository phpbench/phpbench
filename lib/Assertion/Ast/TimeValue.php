<?php

namespace PhpBench\Assertion\Ast;

use PhpBench\Util\TimeUnit;

class TimeValue implements Value
{
    /**
     * @var Value
     */
    private $value;

    /**
     * @var string
     */
    private $unit;

    public function __construct(Value $value, ?string $unit = TimeUnit::MICROSECONDS)
    {
        $this->value = $value;
        $this->unit = $unit;
    }

    public function unit(): string
    {
        return $this->unit;
    }

    public function value(): Value
    {
        return $this->value;
    }

    public static function fromMicroseconds(int $int): self
    {
        return new self(new IntegerNode($int), TimeUnit::MICROSECONDS);
    }
}
