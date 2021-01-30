<?php

namespace PhpBench\Assertion;

final class Token
{
    public const T_NONE = 'none';
    public const T_INTEGER = 'integer';
    public const T_FLOAT = 'float';
    public const T_TOLERANCE = 'tolerance';
    public const T_FUNCTION = 'function';
    public const T_DOT = 'dot';
    public const T_OPEN_PAREN = 'open_paren';
    public const T_CLOSE_PAREN = 'close_paren';
    public const T_COMMA = 'comma';
    public const T_TIME_UNIT = 'time_unit';
    public const T_MEMORY_UNIT = 'memory_unit';
    public const T_COMPARATOR = 'comparator';
    public const T_PROPERTY_ACCESS = 'property_access';
    public const T_PERCENTAGE = 'percentage';
    public const T_AS = 'as';
    public const T_THROUGHPUT = 'throughput';

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $value;

    /**
     * @var int
     */
    public $offset;

    public function __construct(string $type, string $value, int $offset)
    {
        $this->type = $type;
        $this->value = $value;
        $this->offset = $offset;
    }
}
