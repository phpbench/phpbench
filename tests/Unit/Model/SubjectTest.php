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

namespace PhpBench\Tests\Unit\Subject;

use PhpBench\Model\Benchmark;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Subject;
use PHPUnit\Framework\TestCase;

class SubjectTest extends TestCase
{
    private $subject;
    private $benchmark;

    protected function setUp(): void
    {
        $this->benchmark = $this->prophesize(Benchmark::class);
        $this->benchmark->getSubjects()->willReturn([]);
        $this->subject = new Subject($this->benchmark->reveal(), 'subjectOne', 0);
    }

    /**
     * It should say if it is in a set of groups.
     */
    public function testInGroups()
    {
        $this->subject->setGroups(['one', 'two', 'three']);
        $result = $this->subject->inGroups(['five', 'two', 'six']);
        $this->assertTrue($result);

        $result = $this->subject->inGroups(['eight', 'seven']);
        $this->assertFalse($result);

        $result = $this->subject->inGroups([]);
        $this->assertFalse($result);
    }

    /**
     * It should create variants.
     */
    public function testCreateVariant()
    {
        $parameterSet = $this->prophesize(ParameterSet::class);
        $parameterSet->getName()->willReturn('foo');
        $variant = $this->subject->createVariant(
            $parameterSet->reveal(),
            10,
            20
        );

        $this->assertEquals($this->subject, $variant->getSubject());
        $this->assertEquals($parameterSet->reveal(), $variant->getParameterSet());
        $this->assertEquals(0, $variant->count());
        $this->assertEquals(10, $variant->getRevolutions());
        $this->assertEquals(20, $variant->getWarmup());
    }
}
