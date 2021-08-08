<?php

namespace PhpBench\Tests\Unit\Config\Linter;

use PhpBench\Config\Exception\LintError;
use PhpBench\Config\Linter\SeldLinter;
use PHPUnit\Framework\TestCase;

class SeldLinterTest extends TestCase
{
    public function testLintFail(): void
    {
        $this->expectException(LintError::class);
        (new SeldLinter())->lint('foobar.json', '{\'foo\':\'bar\'}');
    }

    public function testLintOk(): void
    {
        (new SeldLinter())->lint('foobar.json', '{"foo":"bar"}');
        $this->addToAssertionCount(1);
    }
}
