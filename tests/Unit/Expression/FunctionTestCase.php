<?php

namespace PhpBench\Tests\Unit\Expression;

use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Expression\Ast\FunctionNode;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\ExpressionFunctions;
use PhpBench\Expression\NodeEvaluator\FunctionEvaluator;

class FunctionTestCase extends ParserTestCase
{
    public function eval(callable $callable, string $argString): PhpValue
    {
        $result = (new FunctionEvaluator(new ExpressionFunctions([
            'func' => $callable
        ])))->evaluate(
            $this->container()->get(Evaluator::class),
            new FunctionNode('func', new ArgumentListNode((function (string $argString) {
                /** @var ArgumentListNode */
                $args = $this->parse(
                    sprintf('[%s]', $argString)
                );

                return $args->nodes();
            })($argString))),
            []
        );

        self::assertInstanceOf(PhpValue::class, $result);

        return $result;
    }
}
