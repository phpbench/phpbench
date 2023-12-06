<?php

namespace PhpBench\Tests\Unit\Expression;

use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\ExpressionFunctions;

class FunctionTestCase extends ParserTestCase
{
    public function eval(callable $callable, string $argString): PhpValue
    {
        // @phpstan-ignore-next-line
        return (new ExpressionFunctions([
            'func' => $callable
        ]))->execute('func', $this->parse(sprintf('[%s]', $argString))->nodes()); // @phpstan-ignore-line
    }
}
