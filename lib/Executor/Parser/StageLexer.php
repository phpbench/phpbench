<?php

namespace PhpBench\Executor\Parser;

use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

final class StageLexer
{
    /**
     * @var string
     */
    private $pattern;

    private const PATTERN_NAME = '(?:[a-z_\/]{1}[a-z0-9_\/]*)';

    private const TOKEN_VALUE_MAP = [
        ';' => Token::T_SEMICOLON,
        '{' => Token::T_OPEN_BRACE,
        '}' => Token::T_CLOSE_BRACE,
    ];

    public const PATTERNS = [
        self::PATTERN_NAME,
    ];

    /**
     */
    public function __construct(
    ) {
        $this->pattern = sprintf(
            '{(%s)|(%s)|\n|\r}iu',
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
            case (preg_match('{'. self::PATTERN_NAME. '}', $value)):
                return Token::T_NAME;

        }

        return $type;
    }
}
