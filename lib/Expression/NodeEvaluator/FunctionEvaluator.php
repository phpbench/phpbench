<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Expression\Ast\FunctionNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\ExpressionFunctions;
use PhpBench\Expression\LazyExpr;
use PhpBench\Expression\LazyFunction;
use PhpBench\Expression\NodeEvaluator;
use RuntimeException;
use Throwable;

class FunctionEvaluator implements NodeEvaluator
{
    final public function __construct(private readonly ExpressionFunctions $functions)
    {
    }

    /**
     * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): ?Node
    {
        if (!$node instanceof FunctionNode) {
            return null;
        }

        $result = $this->doEvaluate($evaluator, $node, $params);

        if (!$result instanceof Node) {
            throw new RuntimeException(sprintf(
                'Function "%s" must return a Node, got "%s"',
                $node->name(),
                gettype($result)
            ));
        }

        return $result;
    }

    /**
     * @param parameters $params
     */
    public function doEvaluate(Evaluator $evaluator, FunctionNode $node, array $params): ?Node
    {
        try {
            $function = $this->functions->get($node->name());

            if ($function instanceof LazyFunction) {
                $args = array_map(function (Node $node) use ($evaluator, $params) {
                    return new LazyExpr($evaluator, $node, $params);
                }, $this->args($node->args()));

                return $function(...$args);
            }

            $result = $function(
                ...
                array_map(function (Node $node) use ($evaluator, $params) {
                    return $evaluator->evaluateType($node, PhpValue::class, $params);
                }, $this->args($node->args()))
            );

            return $result;
        } catch (Throwable $throwable) {
            throw new EvaluationError($node, $throwable->getMessage(), $throwable);
        }
    }

    /**
     * @return array<Node>
     */
    private function args(?ArgumentListNode $args): array
    {
        if (null === $args) {
            return [];
        }

        return $args->nodes();
    }
}
