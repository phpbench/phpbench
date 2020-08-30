<?php

namespace PhpBench\Assertion;

use Doctrine\Common\Lexer\AbstractLexer;

class ExpressionLexer extends AbstractLexer
{
    public const T_NONE = 'none';
    public const T_INTEGER = 'integer';
    public const T_FLOAT = 'float';
    public const T_TOLERANCE = 'tolerance';
    public const T_DOT = 'dot';
    public const T_COMPARATOR = 'comparator';
    public const T_PROPERTY_ACCESS = 'property_access';
    public const T_UNIT = 'unit';
    public const T_PERCENTAGE = 'percentage';

    private const PATTERN_PROPERTY_ACCESS = '(?:[a-z_][a-z0-9_]+\.[a-z_][a-z0-9_]+\.?)+';
    private const PATTERN_COMPARATORS = '(?:<=|>=|<|=|>)';
    private const UNIT = '(?:[a-z\/]+)';
    private const PATTERN_TOLERANCE = '(?:\+\/\-)';

    public function __construct(string $input)
    {
        $this->setInput($input);
    }

    /**
     * {@inheritDoc}
     */
    protected function getCatchablePatterns(): array
    {
        return [
            self::PATTERN_TOLERANCE, // comparators
            self::PATTERN_COMPARATORS, // comparators
            '(?:[0-9]+(?:[\.][0-9]+)*)(?:e[+-]?[0-9]+)?', // numbers
            self::PATTERN_PROPERTY_ACCESS,
            self::UNIT, // names
            '%',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getNonCatchablePatterns(): array
    {
        return ['\s+'];
    }

    /**
     * {@inheritDoc}
     */
    protected function getType(&$value)
    {
        $type = self::T_NONE;

        switch (true) {
            case (is_numeric($value)):
                if (strpos($value, '.') !== false || stripos($value, 'e') !== false) {
                    return self::T_FLOAT;
                }

                return self::T_INTEGER;
            case $value === '+/-':
                return self::T_TOLERANCE;
            case (preg_match('{'. self::PATTERN_PROPERTY_ACCESS . '}', $value)):
                return self::T_PROPERTY_ACCESS;

            case (preg_match('{'. self::PATTERN_COMPARATORS. '}', $value)):
                return self::T_COMPARATOR;
            case (preg_match('{'. self::UNIT. '}', $value)):
                return self::T_UNIT;
            case $value === '%':
                return self::T_PERCENTAGE;
        }

        return $type;
    }
}
