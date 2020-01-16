<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Extensions\XDebug\Tests\Unit\Converter;

use PhpBench\Extensions\XDebug\Converter\TraceToXmlConverter;
use PHPUnit\Framework\TestCase;

class TraceToXmlConverterTest extends TestCase
{
    private $converter;

    protected function setUp(): void
    {
        $this->converter = new TraceToXmlConverter();
    }

    /**
     */
    public function testNoVersion()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected "Version"');
        $this->convert('no_version.xt');
    }

    /**
     */
    public function testNoFileFormat()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected "File format"');
        $this->convert('no_file_format.xt');
    }

    /**
     */
    public function testNoTraceStart()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected "TRACE START"');
        $this->convert('no_trace_start.xt');
    }

    /**
     */
    public function testInvalidNbFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected at least');
        $this->convert('invalid_nb_fields.xt');
    }

    /**
     * It should parse a trace file.
     */
    public function testParse()
    {
        $dom = $this->convert('variance.xt');
        $entryEl = $dom->queryOne('//entry[@level=1]');
        $this->assertNotNull($entryEl);
        $this->assertEquals(1, $entryEl->getAttribute('level'));
        $this->assertEquals(0, $entryEl->getAttribute('func_nb'));
        $this->assertEquals('0.000201', $entryEl->getAttribute('start-time'));
        $this->assertEquals('243848', $entryEl->getAttribute('start-memory'));
        $this->assertEquals('0.016635', $entryEl->getAttribute('end-time'));
        $this->assertEquals('16600', $entryEl->getAttribute('end-memory'));
        $this->assertEquals('243848', $entryEl->getAttribute('start-memory'));
        $this->assertEquals('{main}', $entryEl->getAttribute('function'));
        $this->assertEquals('/tmp/daniel/PhpBenchypQVRT', $entryEl->getAttribute('filename'));

        $entryEl = $dom->queryOne('//entry[@func_nb=777]');
        $this->assertNotNull($entryEl);
        $this->assertEquals(5, $entryEl->getAttribute('level'));
        $this->assertEquals('0.016143', $entryEl->getAttribute('start-time'));
        $this->assertEquals('922488', $entryEl->getAttribute('start-memory'));

        $this->assertEquals('0.016165', $entryEl->getAttribute('end-time'));
        $this->assertEquals('922488', $entryEl->getAttribute('end-memory'));
        $this->assertEquals('922488', $entryEl->getAttribute('start-memory'));
        $this->assertEquals('pow', $entryEl->getAttribute('function'));
        $this->assertEquals('/home/daniel/www/phpbench/phpbench/lib/Math/Statistics.php', $entryEl->getAttribute('filename'));
    }

    private function convert($file)
    {
        return $this->converter->convert(__DIR__ . '/traces/' . $file);
    }
}
