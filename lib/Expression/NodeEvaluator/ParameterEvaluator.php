<?php

namespace PhpBench\Expression\NodeEvaluator;

use ArrayAccess;
use PhpBench\Data\DataFrame;
use PhpBench\Data\Row;
use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\Ast\PhpValueFactory;
use PhpBench\Expression\Ast\PropertyAccessNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\VariableNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\NodeEvaluator;

class ParameterEvaluator implements NodeEvaluator
{
    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): ?Node
    {
        if (!$node instanceof PropertyAccessNode) {
            return null;
        }

        assert($node instanceof PropertyAccessNode);
        $value = self::resolvePropertyAccess($evaluator, $node, $node->segments(), $params);

        if (is_numeric($value)) {
            return PhpValueFactory::fromValue($value);
        }

        if (is_array($value)) {
            return ListNode::fromValues($value);
        }

        if (is_string($value)) {
            return new StringNode($value);
        }

        if (is_null($value)) {
            return new NullNode();
        }

        throw new EvaluationError($node, sprintf(
            'Do not know how to interpret value "%s"', gettype($value)
        ));
    }

    /**
     * @return mixed
     *
     * @param array<string,mixed>|object|scalar $container
     * @param array<Node> $segments
     */
    private static function resolvePropertyAccess(Evaluator $evaluator, Node $node, array $segments, $container)
    {
        $segment = array_shift($segments);
        $value = self::valueFromContainer($evaluator, $node, $container, $segment);

        if (count($segments) === 0) {
            return $value;
        }

        return self::resolvePropertyAccess($evaluator, $node, $segments, $value);
    }

    /**
     * @return int|float|object|array<string,mixed>
     *
     * @param array<string,mixed>|object|scalar $container
     */
    private static function valueFromContainer(Evaluator $evaluator, Node $node, $container, Node $segment)
    {
        $segment = (function (Node $segment) {
            if ($segment instanceof VariableNode) {
                return $segment->name();
            }

            if ($segment instanceof StringNode) {
                return $segment->value();
            }

            if ($segment instanceof IntegerNode) {
                return $segment->value();
            }

            return $segment;
        })($segment);

        if ($segment instanceof Node && $container instanceof DataFrame) {
            return $container->filter(function (Row $row) use ($evaluator, $segment) {
                $result = $evaluator->evaluate($segment, $row->toRecord());

                if (!$result instanceof BooleanNode) {
                    return false;
                }

                return $result->value();
            });
        }

        if ($segment instanceof Node) {
            throw new EvaluationError($node, sprintf(
                'Expression provided but container is not a data frame, it is "%s"',
                gettype($container)
            ));
        }

        if (is_array($container)) {
            if (!array_key_exists($segment, $container)) {
                throw new EvaluationError($node, sprintf(
                    'Array does not have key "%s", it has keys: "%s"',
                    $segment,
                    implode('", "', array_keys($container))
                ));
            }

            return $container[$segment];
        }
        
        if (is_object($container) && method_exists($container, $segment)) {
            return $container->$segment();
        }

        if ($container instanceof ArrayAccess) {
            return $container[$segment];
        }

        throw new EvaluationError($node, sprintf(
            'Could not access "%s" on "%s"',
            $segment,
            is_object($container) ? get_class($container) : gettype($container)
        ));
    }
}
