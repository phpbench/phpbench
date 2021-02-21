<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Expression\Ast\FunctionNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NumberNodeFactory;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\ExpressionFunctions;
use Throwable;

/**
 * @extends AbstractEvaluator<FunctionNode>
 */
class FunctionEvaluator extends AbstractEvaluator
{
    /**
     * @var ExpressionFunctions
     */
    private $functions;

    final public function __construct(ExpressionFunctions $functions)
    {
        $this->functions = $functions;
        parent::__construct(FunctionNode::class);
    }

    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): Node
    {
        try {
            return NumberNodeFactory::fromNumber(
                $this->functions->execute(
                    $node->name(),
                    array_map(function (Node $node) use ($evaluator, $params) {
                        return $this->resolveScalarValues(
                            $evaluator->evaluateType($node, PhpValue::class, $params)
                        );
                    }, $this->args($node->args()))
                )
            );
        } catch (Throwable $throwable) {
            throw new EvaluationError(sprintf(
                'Call to function "%s" failed with error: %s',
                $node->name(),
                $throwable->getMessage()
            ), 0, $throwable);
        }
    }

    /**
     * @return array<mixed>
     */
    private function args(?ArgumentListNode $args)
    {
        if (null === $args) {
            return [];
        }

        return $args->value();
    }

    /**
     * @return mixed
     */
    private function resolveScalarValues(PhpValue $node)
    {
        $value = $node->value();

        if (is_array($value)) {
            return array_map(function (PhpValue $value) {
                return $this->resolveScalarValues($value);
            }, $value);
        }

        return $value;
    }
}
