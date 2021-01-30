<?php

namespace PhpBench\Assertion;

use Doctrine\Common\Lexer\AbstractLexer;

final class ExpressionLexer
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

    /**
     * @var string
     */
    private $pattern;

    private const PATTERN_COMPARATORS = '(?:<=|>=|<|=|>)';
    private const PATTERN_NAME = '(?:[a-z_\/]+)';
    private const PATTERN_TOLERANCE = '(?:\+\/\-)';
    private const PATTERN_THROUGHPUT = '(?:ops\/)';

    private const TOKEN_VALUE_MAP = [
        '+/-' => Token::T_TOLERANCE,
        'ops/' => Token::T_THROUGHPUT,
        '.' => Token::T_DOT,
        '(' => Token::T_OPEN_PAREN,
        ')' => Token::T_CLOSE_PAREN,
        ',' => Token::T_COMMA,
        'as' => Token::T_AS,
        '%' => Token::T_PERCENTAGE,
    ];

    const PATTERNS = [
        '(?:[\(\)])', // parenthesis
        self::PATTERN_TOLERANCE,
        self::PATTERN_COMPARATORS,
        '(?:[0-9]+(?:[\.][0-9]+)*)(?:e[+-]?[0-9]+)?', // numbers
        self::PATTERN_THROUGHPUT,
        self::PATTERN_NAME,
        '%',
        '.',
    ];

    const IGNORE_PATTERNS = [
        '\s+',
    ];

    /**
     * @param string[] $timeUnits
     * @param string[] $functionNames
     * @param string[] $memoryUnits
     */
    public function __construct(
        array $functionNames = [],
        array $timeUnits = [],
        array $memoryUnits = []
    ) {
        $this->functionNames = $functionNames;
        $this->timeUnits = $timeUnits;
        $this->memoryUnits = $memoryUnits;
        $this->pattern = sprintf(
            '{(%s)|%s}',
            implode(')|(', self::PATTERNS),
            implode('|', self::IGNORE_PATTERNS)
        );
    }

    public function lex(string $expression)
    {
        $chunks = (array)preg_split(
            $this->pattern,
            $expression,
            null,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE
        );
        $tokens = [];
        foreach ($chunks as $chunk) {
            [ $value, $offset ] = $chunk;
            $tokens[] = new Token(
                $this->resolveType($value),
                $value,
                $offset
            );
            $prevChunk = $chunk;
        }

        return new Tokens($tokens);
    }

    /**
     * @param mixed $value
     */
    protected function resolveType($value): string
    {
        $type = Token::T_NONE;

        if (array_key_exists($value, self::TOKEN_VALUE_MAP)) {
            return self::TOKEN_VALUE_MAP[$value];
        }

        switch (true) {
            case (is_numeric($value)):
                if (strpos($value, '.') !== false || stripos($value, 'e') !== false) {
                    return Token::T_FLOAT;
                }

                return Token::T_INTEGER;

            case (preg_match('{'. self::PATTERN_NAME. '}', $value)):
                return Token::T_NAME;

            case (preg_match('{'. self::PATTERN_COMPARATORS. '}', $value)):
                return Token::T_COMPARATOR;

            case (in_array($value, $this->functionNames)):
                return Token::T_FUNCTION;

            case (in_array($value, $this->timeUnits)):
                return Token::T_TIME_UNIT;

            case (in_array($value, $this->memoryUnits)):
                return Token::T_MEMORY_UNIT;
        }

        return $type;
    }
}
