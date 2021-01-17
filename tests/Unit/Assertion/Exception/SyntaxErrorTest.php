<?php

namespace PhpBench\Tests\Unit\Assertion\Exception;

use PHPUnit\Framework\TestCase;
use PhpBench\Assertion\Exception\SyntaxError;
use PhpBench\Assertion\ExpressionLexer;

class SyntaxErrorTest extends TestCase
{
    public function testFromToken(): void
    {
        $error = SyntaxError::fromToken('this is an expression', 'invalid', [
            'position' => 5,
            'type' => ExpressionLexer::T_NONE,
            'value' => 'is',
        ]);
        self::assertEquals(<<<'EOT'

this is an expression
-----^
invalid (type: "none", position: 5, value: "is")
EOT
        , $error->getMessage());
    }
}
