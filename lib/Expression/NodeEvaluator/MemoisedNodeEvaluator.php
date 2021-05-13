<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\NodeEvaluator;

class MemoisedNodeEvaluator implements NodeEvaluator
{
    /**
     * @var array<string,Node>
     */
    private $cache = [];

    /**
     * @var NodeEvaluator
     */
    private $innerEvaluator;

    /**
     */
    public function __construct(NodeEvaluator $innerEvaluator)
    {
        $this->innerEvaluator = $innerEvaluator;
    }

    /**
     * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): ?Node
    {
        try {
            $hash = serialize($node) . serialize($params);
        } catch (\Exception $exception) {
            return $this->innerEvaluator->evaluate($evaluator, $node, $params);
        }

        if (isset($this->cache[$hash])) {
            return $this->cache[$hash];
        }

        $this->cache[$hash] = $this->innerEvaluator->evaluate($evaluator, $node, $params);

        return $this->cache[$hash];
    }
}
