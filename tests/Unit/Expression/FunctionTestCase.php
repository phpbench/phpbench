<?php

namespace PhpBench\Tests\Unit\Expression;

use PHPUnit\Framework\TestCase;
use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Expression\ExpressionFunctions;

class FunctionTestCase extends ParserTestCase
{
    public function eval(callable $callable, string $argString)
    {
        return (new ExpressionFunctions([
            'func' => $callable
        ]))->execute('func', new ArgumentListNode(
            $this->parse(sprintf(
                '[%s]',
                $argString
            ))->value()
        ));
    }
}
