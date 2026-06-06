<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;

final class LazyExpr
{
    /**
     * @param parameters $params
     */
    public function __construct(private Evaluator $evaluator, private Node $node, private array $params)
    {
    }
    /**
     * @template TClass of Node
     *
     * @param class-string<TClass> $class
     *
     * @return TClass
     */
    public function expect(string $class): Node
    {
        $result = $this->evaluator->evaluateType($this->node, $class, $this->params);

        if (!$result instanceof $class) {
            throw new \RuntimeException(sprintf(
                'Expected expression to to evaluate to %s, but it evaluated to %s',
                $class,
                $result::class
            ));
        }

        return $result;
    }

    public function evaluate(): Node
    {
        return $this->evaluator->evaluate($this->node, $this->params);
    }
}
