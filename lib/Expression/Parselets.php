<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Exception\ParseletNotFound;

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
     *
     * @return self<PrefixParselet>
     */
    public static function fromPrefixParselets(array $parselets): self
    {
        return new self($parselets, 'prefix');
    }

    /**
     * @param InfixParselet[] $parselets
     *
     * @return self<InfixParselet>
     */
    public static function fromInfixParselets(array $parselets): self
    {
        return new self($parselets, 'infix');
    }

    /**
     * @param SuffixParselet[] $parselets
     *
     * @return self<SuffixParselet>
     */
    public static function fromSuffixParselets(array $parselets): self
    {
        return new self($parselets, 'suffix');
    }

    /**
     * @return T
     */
    public function forToken(Token $token): Parselet
    {
        $parselet = $this->forTokenOrNull($token);

        if (null === $parselet) {
            throw new ParseletNotFound(sprintf(
                'No %s parslet for token type "%s" registered',
                $this->type,
                $token->type
            ));
        }

        return $parselet;
    }

    /**
     * @return T|null
     */
    public function forTokenOrNull(Token $token): ?Parselet
    {
        if (!isset($this->parselets[$token->type])) {
            return null;
        }

        return $this->parselets[$token->type];
    }
}
