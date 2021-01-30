<?php

namespace PhpBench\Tests\Unit\Assertion\Exception;

use PhpBench\Assertion\Exception\SyntaxError;
use PhpBench\Assertion\ExpressionLexer;
use PHPUnit\Framework\TestCase;

class SyntaxErrorTest extends TestCase
{
    public function testFromToken(): void
    {
        $error = SyntaxError::forToken('this is an expression', 'invalid', [
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
