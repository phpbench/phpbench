<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\MainEvaluator;

/**
 * @template T of Node
 * @implements Evaluator<T>
 */
abstract class AbstractEvaluator implements Evaluator
{
    /**
     * @var class-string<T>
     */
    private $nodeFqn;

    /**
     * @param class-string<T> $nodeFqn
     */
    public function __construct(string $nodeFqn)
    {
        $this->nodeFqn = $nodeFqn;
    }

    public function evaluates(Node $node): bool
    {
        return $node instanceof $this->nodeFqn;
    }
}
