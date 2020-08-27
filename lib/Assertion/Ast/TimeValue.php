<?php

namespace PhpBench\Assertion\Ast;

use PhpBench\Assertion\Ast\Microseconds;
use PhpBench\Assertion\Exception\InvalidTimeUnit;

class TimeValue extends Parameter
{
    public const MICROSECONDS = 'microseconds';
    public const MILLISECONDS = 'milliseconds';
    public const SECONDS = 'seconds';
    public const MINUTES = 'minutes';
    public const HOURS = 'hours';
    public const DAYS = 'days';

    /**
     * @var float
     */
    private $value;

    /**
     * @var string
     */
    private $originalUnit;

    /**
     * @var array<string,int>
     */
    private static $map = [
        self::MICROSECONDS => 1,
        self::MILLISECONDS => 1000,
        self::SECONDS => 1000000,
        self::MINUTES => 60000000,
        self::HOURS => 3600000000,
        self::DAYS => 86400000000,
    ];

    public function __construct(float $value, string $originalUnit)
    {
        $this->value = $value;
        $this->originalUnit = $originalUnit;
    }

    public function value(): float
    {
        return $this->value;
    }

    public function originalUnit(): string
    {
        return $this->originalUnit;
    }

    public static function fromValueAndUnit(float $value, string $unit): self
    {
        if (!isset(self::$map[$unit])) {
            throw new InvalidTimeUnit(sprintf(
                'Unknown time unit "%s", known time units "%s"',
                $unit,
                implode('", "', array_keys(self::$map))
            ));
        }

        return new self(self::$map[$unit] * $value, $unit);
    }

    public static function fromMicroseconds(float $microseconds): self
    {
        return self::fromValueAndUnit($microseconds, self::MICROSECONDS);
    }

    public function resolveValue(Arguments $arguments): float
    {
        return $this->value;
    }

    public static function fromMilliseconds(float $milliseconds): self
    {
        return self::fromValueAndUnit($milliseconds, self::MILLISECONDS);
    }

    public static function fromSeconds(int $seconds): self
    {
        return self::fromValueAndUnit($seconds, self::SECONDS);
    }
}
