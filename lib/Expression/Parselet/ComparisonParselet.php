<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\ComparisonNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\InfixParselet;
use PhpBench\Expression\Parser;
use PhpBench\Expression\Tokens;

class ComparisonParselet implements InfixParselet
{
    /**
     * @var string
     */
    private $tokenType;
    /**
     * @var int
     */
    private $precedence;

    public function __construct(string $tokenType, int $precedence)
    {
        $this->tokenType = $tokenType;
        $this->precedence = $precedence;
    }

    public function tokenType(): string
    {
        return $this->tokenType;
    }

    public function parse(Parser $parser, Node $left, Tokens $tokens): Node
    {
        $binaryOperator = $tokens->chomp();
        $right = $parser->parseExpression($tokens, $this->precedence);

        return new ComparisonNode($left, $binaryOperator->value, $right);
    }

    public function precedence(): int
    {
        return $this->precedence;
    }
}
