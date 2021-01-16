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

    /**
     * @var string|null
     */
    private $asUnit;

    public function __construct(Value $value, ?string $unit = TimeUnit::MICROSECONDS, ?string $asUnit = null)
    {
        $this->value = $value;
        $this->unit = TimeUnit::normalizeUnit($unit);
        $this->asUnit = $asUnit ? TimeUnit::normalizeUnit($asUnit) : null;
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

    public function asUnit(): string
    {
        return $this->asUnit ?? $this->unit;
    }
}
