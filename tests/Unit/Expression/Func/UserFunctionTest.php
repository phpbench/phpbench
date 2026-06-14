<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PhpBench\Report\Bridge\ExpressionBridge;
use PhpBench\Tests\Unit\Expression\ParserTestCase;

class UserFunctionTest extends ParserTestCase
{
    public function testEval(): void
    {
        $lang = $this->container([
            'expression.user_functions' => [
                'foobar' => '(foo) => if(foo,1+1,2+2)',
            ],
        ])->get(ExpressionBridge::class);

        self::assertEquals(2, $lang->evaluatePhpValue('foobar(true)', []));
        self::assertEquals(4, $lang->evaluatePhpValue('foobar(false)', []));
    }
}
