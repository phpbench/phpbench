<?php

namespace PhpBench\Expression;

final class Lexer
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
    private const PATTERN_STRING = '"(?:[^"]|"")*"';
    private const PATTERN_NUMBER = '(?:[0-9]+(?:[\\.][0-9]+)*)(?:e[+-]?[0-9]+)?';

    private const TOKEN_VALUE_MAP = [
        '+/-' => Token::T_TOLERANCE,
        '.' => Token::T_DOT,
        '(' => Token::T_OPEN_PAREN,
        ')' => Token::T_CLOSE_PAREN,
        '[' => Token::T_OPEN_LIST,
        ']' => Token::T_CLOSE_LIST,
        ',' => Token::T_COMMA,
        'as' => Token::T_AS,
        '+' => Token::T_PLUS,
        '-' => Token::T_MINUS,
        '*' => Token::T_MULTIPLY,
        '%' => Token::T_PERCENTAGE,
        '/' => Token::T_DIVIDE,
        '>=' => Token::T_GTE,
        '<=' => Token::T_LTE,
        '>' => Token::T_GT,
        '=' => Token::T_EQUALS,
        '<' => Token::T_LT,
        '~' => Token::T_TILDE,
        'and' => Token::T_LOGICAL_OR,
        'true' => Token::T_BOOLEAN,
        'false' => Token::T_BOOLEAN,
        'or' => Token::T_LOGICAL_AND,
        'ops/' => Token::T_THROUGHPUT,
        ' ' => Token::T_WHITESPACE,
    ];

    const PATTERNS = [
        self::PATTERN_NUMBER, // numbers
        self::PATTERN_FUNCTION,
        self::PATTERN_NAME,
        self::PATTERN_STRING,
        '\.',
    ];

    /**
     * @param string[] $timeUnits
     * @param string[] $memoryUnits
     */
    public function __construct(
        array $timeUnits = [],
        array $memoryUnits = []
    ) {
        $this->timeUnits = $timeUnits;
        $this->memoryUnits = $memoryUnits;
        $this->pattern = sprintf(
            '{(%s)|(%s)|\n}iu',
            implode(')|(', array_map(function (string $value) {
                return preg_quote($value);
            }, array_keys(self::TOKEN_VALUE_MAP))),
            implode(')|(', self::PATTERNS)
        );
    }

    public function lex(string $expression): Tokens
    {
        $chunks = (array)preg_split(
            $this->pattern,
            $expression,
            -1,
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
            case ($value[0] === '"'):
                return Token::T_STRING;

            case (in_array($value, $this->timeUnits, true)):
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
