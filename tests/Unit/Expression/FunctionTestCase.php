<?php

namespace PhpBench\Tests\Unit\Expression;

use PhpBench\Expression\ExpressionFunctions;

class FunctionTestCase extends ParserTestCase
{
    public function eval(callable $callable, string $argString)
    {
        return (new ExpressionFunctions([
            'func' => $callable
        ]))->execute('func', $this->parse(sprintf('[%s]', $argString))->nodes());
    }
}
