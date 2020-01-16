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

namespace PhpBench\Tests\Unit\Benchmark\Remote;

use PhpBench\Benchmark\Remote\ReflectionClass;
use PhpBench\Benchmark\Remote\ReflectionHierarchy;
use PhpBench\Benchmark\Remote\ReflectionMethod;
use PHPUnit\Framework\TestCase;

class ReflectionHierarchyTest extends TestCase
{
    private $hierarchy;
    private $reflection1;
    private $reflection2;

    protected function setUp(): void
    {
        $this->hierarchy = new ReflectionHierarchy();
        $this->reflection1 = new ReflectionClass();
        $this->reflection2 = new ReflectionClass();
    }

    /**
     * It can have reflection classes added to it.
     * It is iterable.
     * It should get the top reflection.
     */
    public function testAddReflectionsAndIterate()
    {
        $this->hierarchy->addReflectionClass($this->reflection1);
        $this->hierarchy->addReflectionClass($this->reflection2);

        foreach ($this->hierarchy as $index => $reflectionClass) {
            $this->assertInstanceOf('PhpBench\Benchmark\Remote\ReflectionClass', $reflectionClass);
        }

        $this->assertEquals(1, $index);

        $top = $this->hierarchy->getTop();
        $this->assertSame($this->reflection1, $top);
    }

    /**
     * It should throw an exception if there are no classes and the top is requested.
     *
     */
    public function testGetTopNoClasses()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot get top');
        $this->hierarchy->getTop();
    }

    /**
     * It can determine if a method exists.
     */
    public function testHasMethod()
    {
        $this->reflection1->methods['foobar'] = true;
        $this->hierarchy->addReflectionClass($this->reflection1);
        $this->hierarchy->addReflectionClass($this->reflection2);

        $this->assertTrue($this->hierarchy->hasMethod('foobar'));
        $this->assertFalse($this->hierarchy->hasMethod('barfoo'));

        $this->reflection2->methods['barfoo'] = true;
        $this->assertTrue($this->hierarchy->hasMethod('barfoo'));
    }

    /**
     * It can determine if a method is static.
     */
    public function testHasStaticMethod()
    {
        $this->reflection1->methods['foobar'] = new ReflectionMethod();
        $this->reflection1->methods['foobar']->isStatic = true;
        $this->hierarchy->addReflectionClass($this->reflection1);
        $this->hierarchy->addReflectionClass($this->reflection2);

        $this->assertTrue($this->hierarchy->hasStaticMethod('foobar'));
        $this->assertFalse($this->hierarchy->hasStaticMethod('barfoo'));

        $this->reflection1->methods['barfoo'] = new ReflectionMethod();
        $this->reflection1->methods['barfoo']->isStatic = true;
        $this->assertTrue($this->hierarchy->hasStaticMethod('barfoo'));
    }
}
