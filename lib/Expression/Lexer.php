<?php

namespace PhpBench\Expression;

final class Lexer
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * @var array
     */
    private $unitNames;

    private const PATTERN_NAME = '(?:[a-z_\/]{1}[a-z0-9_\/]*)';
    private const PATTERN_FUNCTION = '(?:[a-z_\/]+\()';
    private const PATTERN_STRING = '"(?:[^"]|"")*"';
    private const PATTERN_STRING_SINGLE = '\'(?:[^\']|\'\')*\'';
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
        '?' => Token::T_QUESTION,
        'and' => Token::T_LOGICAL_OR,
        'true' => Token::T_BOOLEAN,
        'false' => Token::T_BOOLEAN,
        'or' => Token::T_LOGICAL_AND,
        'ops/' => Token::T_THROUGHPUT,
        ' ' => Token::T_WHITESPACE,
        'null' => Token::T_NULL,
    ];

    public const PATTERNS = [
        self::PATTERN_NUMBER, // numbers
        self::PATTERN_FUNCTION,
        self::PATTERN_NAME,
        self::PATTERN_STRING,
        self::PATTERN_STRING_SINGLE,
        '\.',
    ];

    /**
     */
    public function __construct(
        array $unitNames = []
    ) {
        $this->pattern = sprintf(
            '{(%s)|(%s)|\n|\r}iu',
            implode(')|(', array_map(function (string $value) {
                return preg_quote($value);
            }, array_keys(self::TOKEN_VALUE_MAP))),
            implode(')|(', self::PATTERNS)
        );
        $this->unitNames = $unitNames;
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

        $prevToken = new Token(Token::T_EOF, '', 0);

        foreach ($chunks as $chunk) {
            [ $value, $offset ] = $chunk;
            $prevToken = new Token(
                $this->resolveType($value, $prevToken),
                $value,
                $offset
            );
            $tokens[] = $prevToken;
            $prevChunk = $chunk;
        }

        return new Tokens($tokens);
    }

    /**
     * @param mixed $value
     */
    protected function resolveType($value, Token $prevToken): string
    {
        $type = Token::T_NONE;

        if (array_key_exists($value, self::TOKEN_VALUE_MAP)) {
            return self::TOKEN_VALUE_MAP[$value];
        }

        switch (true) {
            case (is_numeric($value)):
                if (strpos((string)$value, '.') !== false || stripos((string)$value, 'e') !== false) {
                    return Token::T_FLOAT;
                }

                return Token::T_INTEGER;
            case ($value[0] === '"' || $value[0] === '\''):
                return Token::T_STRING;

            case ($prevToken->type !== Token::T_DOT && in_array($value, $this->unitNames, true)):
                return Token::T_UNIT;

            case (preg_match('{'. self::PATTERN_FUNCTION. '}', $value)):
                return Token::T_FUNCTION;

            case (preg_match('{'. self::PATTERN_NAME. '}', $value)):
                return Token::T_NAME;
        }

        return $type;
    }
}
