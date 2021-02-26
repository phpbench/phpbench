<?php

namespace PhpBench\Expression;

final class SyntaxHighlighter
{
    /**
     * @var array<string,string> $tokenColorMap
     */
    private $tokenColorMap;

    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @param array<string,string> $tokenColorMap
     */
    public function __construct(Lexer $lexer, array $tokenColorMap)
    {
        $this->tokenColorMap = $tokenColorMap;
        $this->lexer = $lexer;
    }

    public function highlight(string $expression): string
    {
        $tokens = $this->lexer->lex($expression);

        return implode('', array_map(function (Token $token) {
            if (array_key_exists($token->type, $this->tokenColorMap)) {
                return sprintf(
                    '<fg=%s>%s</>',
                    $this->tokenColorMap[$token->type],
                    $token->value
                );
            }

            return $token->value;
        }, $tokens->toArray()));
    }
}
