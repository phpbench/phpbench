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

use PhpBench\Formatter\ClassLoader;
use PHPUnit\Framework\TestCase;

class ClassLoaderTest extends TestCase
{
    private $loader;

    public function setUp()
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
     * @expectedException Seld\JsonLint\ParsingException
     */
    public function testInvalidJson()
    {
        $this->loader->load(__DIR__ . '/class/invalid.json');
    }

    /**
     * It should throw an exception if the file does not exist.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage does not exist.
     */
    public function testNotExist()
    {
        $this->loader->load(__DIR__ . '/class/not_exists.json');
    }
}
