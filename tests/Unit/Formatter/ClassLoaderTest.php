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

namespace PhpBench\Tests\Unit\Formatter;

use InvalidArgumentException;
use PhpBench\Formatter\ClassLoader;
use PHPUnit\Framework\TestCase;
use Seld\JsonLint\ParsingException;

class ClassLoaderTest extends TestCase
{
    private $loader;

    protected function setUp(): void
    {
        $this->loader = new ClassLoader();
    }

    /**
     * It should load a class fiel.
     */
    public function testLoadClassFile()
    {
        $classes = $this->loader->load(__DIR__ . '/class/valid.json');
        $this->assertEquals([
            'foo' => [
                ['printf', ['option_1' => 'value_1']],
            ],
            'bar' => [
                ['printf', ['option_1' => 'value_1']],
            ],
        ], $classes);
    }

    /**
     * It should throw an exception if invalid json is given.
     *
     */
    public function testInvalidJson()
    {
        $this->expectException(ParsingException::class);
        $this->loader->load(__DIR__ . '/class/invalid.json');
    }

    /**
     * It should throw an exception if the file does not exist.
     *
     */
    public function testNotExist()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not exist.');
        $this->loader->load(__DIR__ . '/class/not_exists.json');
    }
}
