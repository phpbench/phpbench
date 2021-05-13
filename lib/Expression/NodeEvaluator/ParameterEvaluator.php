<?php

namespace PhpBench\Expression\NodeEvaluator;

use ArrayAccess;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\Ast\PropertyAccessNode;
use PhpBench\Expression\Ast\PhpValueFactory;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\VariableNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\NodeEvaluator;
use RuntimeException;

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
        $value = self::resolvePropertyAccess($node, $node->segments(), $params);

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
    private static function resolvePropertyAccess(Node $node, array $segments, $container)
    {
        $segment = array_shift($segments);
        $value = self::valueFromContainer($node, $container, $segment);

        if (count($segments) === 0) {
            return $value;
        }

        return self::resolvePropertyAccess($node, $segments, $value);
    }

    /**
     * @return int|float|object|array<string,mixed>
     *
     * @param array<string,mixed>|object|scalar $container
     */
    private static function valueFromContainer(Node $node, $container, Node $segment)
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

            throw new RuntimeException(sprintf(
                'Do not know how to interpret property access with "%s"', get_class($segment)
            ));
                
        })($segment);

        if (is_array($container)) {
            if (
                !array_key_exists($segment, $container) ||
                (
                    $container instanceof ArrayAccess &&
                    !$container->offsetExists($segment)
                )

            ) {
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
