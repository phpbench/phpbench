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
use PhpBench\Expression\Ast\NullSafeNode;
use PhpBench\Expression\Ast\ParameterNode;
use PhpBench\Expression\Ast\PhpValueFactory;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\VariableNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\Exception\KeyDoesNotExist;
use PhpBench\Expression\NodeEvaluator;

class ParameterEvaluator implements NodeEvaluator
{
    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): ?Node
    {
        if (!$node instanceof ParameterNode) {
            return null;
        }

        assert($node instanceof ParameterNode);
        $value = $this->resolvePropertyAccess($evaluator, $node, $node->segments(), $params);

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

        if ($value instanceof DataFrame) {
            return new ListNode(array_map(function (Row $row) {
                return PhpValueFactory::fromValue($row->toSeries()->toValues());
            }, $value->rows()));
        }

        throw new EvaluationError($node, sprintf(
            'Do not know how to interpret value "%s"', is_object($value) ? get_class($value) : gettype($value)
        ));
    }

    /**
     * @return mixed
     *
     * @param array<string,mixed>|object|scalar $container
     * @param array<Node> $segments
     */
    private function resolvePropertyAccess(Evaluator $evaluator, Node $node, array $segments, $container)
    {
        $segment = array_shift($segments);
        $value = $this->valueFromContainer($evaluator, $node, $container, $segment);

        if (count($segments) === 0) {
            return $value;
        }

        return $this->resolvePropertyAccess($evaluator, $node, $segments, $value);
    }

    /**
     * @return int|float|object|array<string,mixed>
     *
     * @param array<string,mixed>|null|object|scalar $container
     */
    private function valueFromContainer(Evaluator $evaluator, Node $node, $container, Node $segment)
    {
        if ($segment instanceof NullSafeNode) {
            try {
                return $this->valueFromContainer($evaluator, $node, $container, $segment->variable());
            } catch (KeyDoesNotExist $notExist) {
                return null;
            }
        }
        if ($segment instanceof VariableNode) {
            return $this->containerValue($node, $container, $segment->name());
        }

        if ($segment instanceof StringNode) {
            return $this->containerValue($node, $container, $segment->value());
        }

        if ($segment instanceof IntegerNode) {
            return $this->containerValue($node, $container, $segment->value());
        }

        return $this->filterContainerByExpression($container, $evaluator, $segment, $node);
    }

    /**
     * @param int|string $segment
     *
     * @return mixed
     */
    private function containerValue(Node $node, $container, $segment)
    {
        if (is_array($container)) {
            return $this->valueFromArray($segment, $container, $node);
        }

        if (is_object($container) && method_exists($container, (string)$segment)) {
            return $this->valueFromMethod($container, $segment);
        }

        if ($container instanceof ArrayAccess) {
            return $this->valueFromArrayAccess($container, $segment, $node);
        }

        throw new KeyDoesNotExist($node, sprintf(
            'Could not access "%s" on "%s"',
            $segment,
            is_object($container) ? get_class($container) : gettype($container)
        ));
    }

    private function filterContainerByExpression($container, Evaluator $evaluator, Node $segment, Node $node): DataFrame
    {
        if (!$container instanceof DataFrame) {
            throw new EvaluationError($node, sprintf(
                'Expression provided but container is not a data frame, it is "%s"',
                gettype($container)
            ));
        }

        return $container->filter(function (Row $row) use ($evaluator, $segment) {
            $result = $evaluator->evaluate($segment, $row->toRecord());

            if (!$result instanceof BooleanNode) {
                return false;
            }

            return $result->value();
        });
    }

    private function valueFromArray($segment, array $container, Node $node)
    {
        if (!array_key_exists($segment, $container)) {
            throw new KeyDoesNotExist($node, sprintf(
                'Array does not have key "%s", it has keys: "%s"',
                $segment,
                implode('", "', array_keys($container))
            ));
        }

        return $container[$segment];
    }

    private function valueFromMethod(object $container, $segment)
    {
        return $container->$segment();
    }

    /**
     * @return mixed
     */
    private function valueFromArrayAccess(ArrayAccess $container, $segment, Node $node)
    {
        if (!$container->offsetExists($segment)) {
            throw new KeyDoesNotExist($node, sprintf(
                'Array-access object does not have key "%s"',
                $segment
            ));
        }

        return $container[$segment];
    }
}
