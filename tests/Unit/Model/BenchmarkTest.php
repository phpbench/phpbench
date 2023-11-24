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

namespace PhpBench\Tests\Unit\Model;

use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Model\Benchmark;
use PhpBench\Model\ResolvedExecutor;
use PhpBench\Model\Suite;
use PhpBench\Registry\Config;
use PhpBench\Tests\TestCase;

class BenchmarkTest extends TestCase
{
    public const EXAMPLE_FORMAT = 'foobar';

    /**
     * @var Benchmark
     */
    private $benchmark;
    private $suite;

    protected function setUp(): void
    {
        $this->suite = $this->prophesize(Suite::class);
        $this->benchmark = new Benchmark($this->suite->reveal(), 'Class');
    }

    public function testGetSet(): void
    {
        $this->assertSame($this->suite->reveal(), $this->benchmark->getSuite());
        $this->assertEquals('Class', $this->benchmark->getClass());
    }

    /**
     * @dataProvider provideName
     */
    public function testGetName(string $class, string $expected): void
    {
        $benchmark = new Benchmark($this->suite->reveal(), $class);
        self::assertEquals($expected, $benchmark->getName());
    }

    /**
     * @return array<array<string>>
     */
    public static function provideName(): array
    {
        return [
            [ '', '' ],
            [ 'A', 'A' ],
            [ 'A\\B', 'B' ],
            [ 'A\\B\\Code', 'Code' ],
        ];
    }

    /**
     * It should create and add a subject from some metadata.
     */
    public function testCreateSubjectFromMetadata(): void
    {
        $metadata = $this->prophesize(SubjectMetadata::class);
        $metadata->getName()->willReturn('hello');
        $metadata->getGroups()->willReturn(['one', 'two']);
        $metadata->getSleep()->willReturn(30);
        $metadata->getFormat()->willReturn(self::EXAMPLE_FORMAT);
        $metadata->getRetryThreshold()->willReturn(10);
        $metadata->getOutputTimeUnit()->willReturn(50);
        $metadata->getOutputMode()->willReturn(60);
        $metadata->getOutputTimePrecision()->willReturn(3);

        $executor = ResolvedExecutor::fromNameAndConfig('foo', new Config('one', ['foo' => 'bar']));

        $subject = $this->benchmark->createSubjectFromMetadataAndExecutor($metadata->reveal(), $executor);
        $this->assertInstanceOf('PhpBench\Model\Subject', $subject);
        $this->assertEquals('hello', $subject->getName());
        $this->assertEquals(['one', 'two'], $subject->getGroups());
        $this->assertEquals(30, $subject->getSleep());
        $this->assertEquals(self::EXAMPLE_FORMAT, $subject->getFormat());
        $this->assertEquals(10, $subject->getRetryThreshold());
        $this->assertEquals(50, $subject->getOutputTimeUnit());
        $this->assertEquals(60, $subject->getOutputMode());
        $this->assertEquals(3, $subject->getOutputTimePrecision());

        $subjects = $this->benchmark->getSubjects();
        $this->assertCount(1, $subjects);
        $bSubject = current($subjects);
        $this->assertInstanceOf('PhpBench\Model\Subject', $bSubject);
        $this->assertSame($subject, $bSubject);
    }
}
