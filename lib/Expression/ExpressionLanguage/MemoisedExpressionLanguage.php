<?php

namespace PhpBench\Expression\ExpressionLanguage;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\ExpressionLanguage;

class MemoisedExpressionLanguage implements ExpressionLanguage
{
    /**
     * @var array<string, Node>
     */
    private $cache = [];

    /**
     * @var ExpressionLanguage
     */
    private $inner;

    public function __construct(ExpressionLanguage $inner)
    {
        $this->inner = $inner;
    }

    public function parse(string $expression): Node
    {
        if (isset($this->cache[$expression])) {
            return $this->cache[$expression];
        }

        $this->cache[$expression] = $this->inner->parse($expression);

        return $this->cache[$expression];
    }
}
