<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\ParameterNode;
use PhpBench\Expression\Ast\PhpValueFactory;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\EvaluationError;

/**
 * @extends AbstractEvaluator<ParameterNode>
 */
class ParameterEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(ParameterNode::class);
    }

    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): Node
    {
        $value = self::resolvePropertyAccess($node, $node->segments(), $params);

        if (is_numeric($value)) {
            return PhpValueFactory::fromNumber($value);
        }

        if (is_array($value)) {
            return ListNode::fromValues($value);
        }

        if (is_string($value)) {
            return new StringNode($value);
        }

        throw new EvaluationError($node, sprintf(
            'Do not know how to interpret value "%s"', gettype($value)
        ));
    }

    /**
     * @return mixed
     *
     * @param array<string,mixed>|object|scalar $container
     * @param array<string> $segments
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
    private static function valueFromContainer(Node $node, $container, string $segment)
    {
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

        throw new EvaluationError($node, sprintf(
            'Could not access "%s" on "%s"',
            $segment,
            is_object($container) ? get_class($container) : gettype($container)
        ));
    }
}
