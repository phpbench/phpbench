<?php

namespace PhpBench\Tests\Unit\Config\Linter;

use PhpBench\Config\Exception\LintError;
use PhpBench\Config\Linter\JsonDecodeLinter;
use PHPUnit\Framework\TestCase;

class JsonDecodeLinterTest extends TestCase
{
    public function testLintFail(): void
    {
        $this->expectException(LintError::class);
        (new JsonDecodeLinter())->lint('foobar.json', '{\'foo\':\'bar\'}');
    }

    public function testLintOk(): void
    {
        (new JsonDecodeLinter())->lint('foobar.json', '{"foo":"bar"}');
        $this->addToAssertionCount(1);
    }
}
