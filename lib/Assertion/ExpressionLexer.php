<?php

namespace PhpBench\Assertion;

use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

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

    private const PATTERN_NAME = '(?:[a-z_\/]+)';
    private const PATTERN_FUNCTION = '(?:[a-z_\/]+\()';

    private const TOKEN_VALUE_MAP = [
        '+/-' => Token::T_TOLERANCE,
        '.' => Token::T_DOT,
        '(' => Token::T_OPEN_PAREN,
        ')' => Token::T_CLOSE_PAREN,
        '[' => Token::T_OPEN_LIST,
        ']' => Token::T_CLOSE_LIST,
        ',' => Token::T_COMMA,
        'as' => Token::T_AS,
        '%' => Token::T_PERCENTAGE,
        '+' => Token::T_PLUS,
        '-' => Token::T_MINUS,
        '*' => Token::T_MULTIPLY,
        '/' => Token::T_DIVIDE,
        '>=' => Token::T_GTE,
        '<=' => Token::T_LTE,
        '>' => Token::T_GT,
        '=' => Token::T_EQUALS,
        '<' => Token::T_OPERATOR,
        '[' => Token::T_LIST_START,
        ']' => Token::T_LIST_END,
        'ops/' => Token::T_THROUGHPUT,
    ];

    const PATTERNS = [
        '(?:[\(\)])', // parenthesis
        '(?:[0-9]+(?:[\.][0-9]+)*)(?:e[+-]?[0-9]+)?', // numbers
        self::PATTERN_FUNCTION,
        self::PATTERN_NAME,
        '%',
        '\.',
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
            '{(%s)|(%s)|%s}iu',
            implode(')|(', array_map(function (string $value) {
                return preg_quote($value);
            }, array_keys(self::TOKEN_VALUE_MAP))),
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

            case (in_array($value, $this->functionNames)):
                return Token::T_FUNCTION;

            case (in_array($value, $this->timeUnits)):
                return Token::T_UNIT;

            case (in_array($value, $this->memoryUnits)):
                return Token::T_UNIT;

            case (preg_match('{'. self::PATTERN_FUNCTION. '}', $value)):
                return Token::T_FUNCTION;

            case (preg_match('{'. self::PATTERN_NAME. '}', $value)):
                return Token::T_NAME;

        }

        return $type;
    }
}
