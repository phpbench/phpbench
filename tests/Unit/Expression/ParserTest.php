<?php

namespace PhpBench\Tests\Unit\Expression;

use PhpBench\Expression\Exception\SyntaxError;

class ParserTest extends ParserTestCase
{
    public function testPrefixSyntaxError(): void
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage(<<<'EOT'
Unexpected "name" token:

    bar foo
    ^^^
EOT
        );

        $this->parse('bar foo');
    }

    public function testTrailingMatter(): void
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage(<<<'EOT'
Unexpected "name" at end of expression:
EOT
        );

        $this->parse('1 * 2 nasdfnuasdf');
    }
}
