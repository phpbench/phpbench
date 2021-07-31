<?php

namespace PhpBench\Tests\Unit\Opcache;

use PHPUnit\Framework\TestCase;
use PhpBench\Opcache\OpcodeDebugParser;

class OpcodeDebugParserTest extends TestCase
{
    public function testCountOpcodes(): void
    {
        $count = (new OpcodeDebugParser())->countOpcodes(file_get_contents(__DIR__ . '/examples/assertBench'));
        self::assertEquals(25203, $count);
    }
}
