<?php

namespace PhpBench\Expression;

use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Token;
use PhpBench\Assertion\Tokens;
use RuntimeException;

/**
 * @template T of Parselet
 */
final class Parselets
{
    /**
     * @var array<string,T>
     */
    private $parselets;

    /**
     * @var string
     */
    private $type;

    /**
     * @param T[] $parselets
     */
    private function __construct(array $parselets, string $type)
    {
        foreach ($parselets as $parselet) {
            $this->parselets[$parselet->tokenType()] = $parselet;
        }

        $this->type = $type;
    }

    /**
     * @param PrefixParselet[] $parselets
     * @return self<PrefixParselet>
     */
    public static function fromPrefixParselets(array $parselets): self
    {
        return new self($parselets, 'prefix');
    }

    /**
     * @param InfixParselet[] $parselets
     * @return self<InfixParselet>
     */
    public static function fromInfixParselets(array $parselets): self
    {
        return new self($parselets, 'infix');
    }

    /**
     * @return T
     */
    public function forToken(Token $token): Parselet
    {
        if (!isset($this->parselets[$token->type])) {
            throw new RuntimeException(sprintf(
                'No %s parslet for token type "%s" registered',
                $this->type,
                $token->type
            ));
        }

        return $this->parselets[$token->type];
    }
}
