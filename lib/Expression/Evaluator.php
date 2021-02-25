<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;

interface Evaluator
{
    /**
     * @template T of Node
     *
     * @param class-string<T> $expectedType
     * @param parameters $params
     *
     * @return T
     */
    public function evaluateType(Node $node, string $expectedType, array $params): Node;

    /**
     * @param parameters $params
     */
    public function evaluate(Node $node, array $params): Node;
}
