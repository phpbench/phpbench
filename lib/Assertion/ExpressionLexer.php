<?php

namespace PhpBench\Assertion;

use Doctrine\Common\Lexer\AbstractLexer;

class ExpressionLexer extends AbstractLexer
{
    /**
     * @var string[]
     */
    private $functionNames;

    /**
     * @var string[]
     */
    private $timeUnits;

    /**
     * @var string[]
     */
    private $memoryUnits;

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

    private const PATTERN_PROPERTY_ACCESS = '(?:[a-z_][a-z0-9_]+\.(?:[a-z_][a-z0-9_]+\.?)+)';
    private const PATTERN_COMPARATORS = '(?:<=|>=|<|=|>)';
    private const PATTERN_NAME = '(?:[a-z_\/]+)';
    private const PATTERN_TOLERANCE = '(?:\+\/\-)';

    /**
     * @param string[] $timeUnits
     * @param string[] $functionNames
     * @param string[] $memoryUnits
     */
    public function __construct(
        array $functionNames = [],
        array $timeUnits = [],
        array $memoryUnits = []
    )
    {
        $this->functionNames = $functionNames;
        $this->timeUnits = $timeUnits;
        $this->memoryUnits = $memoryUnits;
    }

    protected function getCatchablePatterns(): array
    {
        return [
            '(?:[\(\)])', // parenthesis
            self::PATTERN_TOLERANCE, // comparators
            self::PATTERN_COMPARATORS, // comparators
            '(?:[0-9]+(?:[\.][0-9]+)*)(?:e[+-]?[0-9]+)?', // numbers
            self::PATTERN_PROPERTY_ACCESS,
            self::PATTERN_NAME, // names
            '%',
        ];
    }

    protected function getNonCatchablePatterns(): array
    {
        return ['\s+'];
    }

    protected function getType(&$value)
    {
        $type = self::T_NONE;

        switch (true) {
            case (is_numeric($value)):
                if (strpos($value, '.') !== false || stripos($value, 'e') !== false) {
                    return self::T_FLOAT;
                }

                return self::T_INTEGER;

            case $value === '(':
                return self::T_OPEN_PAREN;

            case $value === ',':
                return self::T_COMMA;

            case $value === ')':
                return self::T_CLOSE_PAREN;

            case $value === '+/-':
                return self::T_TOLERANCE;

            case (preg_match('{'. self::PATTERN_PROPERTY_ACCESS . '}', $value)):
                return self::T_PROPERTY_ACCESS;

            case (preg_match('{'. self::PATTERN_COMPARATORS. '}', $value)):
                return self::T_COMPARATOR;

            case (in_array($value, $this->functionNames)):
                return self::T_FUNCTION;

            case (in_array($value, $this->timeUnits)):
                return self::T_TIME_UNIT;

            case (in_array($value, $this->memoryUnits)):
                return self::T_MEMORY_UNIT;

            case $value === '%':
                return self::T_PERCENTAGE;
        }

        return $type;
    }
}
