<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\NodeEvaluator;
use function spl_object_hash;

/**
 * @implements NodeEvaluator<Node>
 */
class MemoisedNodeEvaluator implements NodeEvaluator
{
    /**
     * @var array<string,Node>
     */
    private $cache = [];

    /**
     * @var NodeEvaluator<Node>
     */
    private $innerEvaluator;

    /**
     * @param NodeEvaluator<Node> $innerEvaluator
     */
    public function __construct(NodeEvaluator $innerEvaluator)
    {
        $this->innerEvaluator = $innerEvaluator;
    }

    public function evaluates(Node $node): bool
    {
        return $this->innerEvaluator->evaluates($node);
    }

    /**
     * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): Node
    {
        // cache based on the value of the node
        $hash = serialize($node);

        if (isset($this->cache[$hash])) {
            return $this->cache[$hash];
        }

        $this->cache[$hash] = $this->innerEvaluator->evaluate($evaluator, $node, $params);

        return $this->cache[$hash];
    }
}
