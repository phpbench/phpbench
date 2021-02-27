<?php

namespace PhpBench\Expression;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use PhpBench\Expression\Exception\SyntaxError;

/**
 * @implements IteratorAggregate<int, Token>
 */
final class Tokens implements IteratorAggregate, Countable
{
    /**
     * @var Token[]
     */
    private $tokens;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @param Token[] $tokens
     */
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * @return Token[]
     */
    public function toArray(): array
    {
        return $this->tokens;
    }

    /**
     * @return ArrayIterator<int,Token>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->tokens);
    }

    /**
     * Return the current token and move the position ahead.
     */
    public function chomp(?string $type = null): ?Token
    {
        $previous = $this->previous();
        $token = $this->atPosition($this->position++);

        if (null !== $type && $token->type !== $type) {
            throw SyntaxError::forToken($this, $previous, sprintf(
                'Expected type "%s" after, got "%s"',
                $type,
                $token->type
            ));
        }

        return $token;
    }

    public function count(): int
    {
        return count($this->tokens);
    }

    public function toString(): string
    {
        $last = $this->tokens[count($this->tokens) - 1];

        if (!$last instanceof Token) {
            return '';
        }

        $out = str_repeat(' ', $last->end());

        foreach ($this as $token) {
            $out = substr_replace($out, $token->value, $token->start(), $token->length());
        }

        return $out;
    }

    private function atPosition(int $position): Token
    {
        if (isset($this->tokens[$position])) {
            return $this->tokens[$position];
        }

        return new Token(Token::T_EOF, '', $position);
    }

    public function current(): Token
    {
        return $this->atPosition($this->position);
    }

    public function previous(): Token
    {
        return $this->atPosition($this->position - 1);
    }

    public function hasMore(): bool
    {
        return $this->position < count($this->tokens);
    }

    public function withoutWhitespace(): self
    {
        return new self(array_values(array_filter($this->tokens, function (Token $token) {
            return $token->type !== Token::T_WHITESPACE;
        })));
    }
}
