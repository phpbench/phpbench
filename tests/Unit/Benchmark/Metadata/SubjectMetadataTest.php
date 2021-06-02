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

namespace PhpBench\Tests\Unit\Benchmark\Metadata;

use Generator;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\ExecutorMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Tests\TestCase;

class SubjectMetadataTest extends TestCase
{
    private $subject;

    /**
     * @var BenchmarkMetadata
     */
    private $benchmark;

    protected function setUp(): void
    {
        $this->benchmark = $this->prophesize(BenchmarkMetadata::class);
        $this->subject = new SubjectMetadata($this->benchmark->reveal(), 'subjectOne', 0);
    }

    /**
     * It should say if it is in a set of groups.
     */
    public function testInGroups(): void
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
     * @dataProvider provideMerge
     */
    public function testMerge(callable $confgurator, callable $assertion): void
    {
        $subject1 = new SubjectMetadata(
            $this->benchmark->reveal(),
            'one'
        );
        $subject2 = new SubjectMetadata(
            $this->benchmark->reveal(),
            'one'
        );

        $confgurator($subject1, $subject2);
        $subject1->merge($subject2);
        $assertion($subject1);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideMerge(): Generator
    {
        yield [
            function (SubjectMetadata $subject1, SubjectMetadata $subject2): void {
                $subject1->setGroups(['one', 'two']);
                $subject2->setGroups(['three', 'four']);
            },
            function (SubjectMetadata $merged): void {
                self::assertEquals([
                    'one',
                    'two',
                    'three',
                    'four'
                ], $merged->getGroups());
            }
        ];

        yield [
            function (SubjectMetadata $subject1, SubjectMetadata $subject2): void {
                $subject1->setBeforeMethods(['one', 'two']);
                $subject2->setBeforeMethods(['three', 'four']);
            },
            function (SubjectMetadata $merged): void {
                self::assertEquals(['one', 'two', 'three', 'four'], $merged->getBeforeMethods());
            }
        ];

        yield [
            function (SubjectMetadata $subject1, SubjectMetadata $subject2): void {
                $subject1->setAfterMethods(['one', 'two']);
                $subject2->setAfterMethods(['three', 'four']);
            },
            function (SubjectMetadata $merged): void {
                self::assertEquals(['one', 'two', 'three', 'four'], $merged->getAfterMethods());
            }
        ];

        yield [
            function (SubjectMetadata $subject1, SubjectMetadata $subject2): void {
                $subject1->setParamProviders(['one', 'two']);
                $subject2->setParamProviders(['three', 'four']);
            },
            function (SubjectMetadata $merged): void {
                self::assertEquals(['one', 'two', 'three', 'four'], $merged->getParamProviders());
            }
        ];

        yield [
            function (SubjectMetadata $subject1, SubjectMetadata $subject2): void {
                $subject1->setIterations([1, 2]);
                $subject2->setIterations([3, 4]);
            },
            function (SubjectMetadata $merged): void {
                self::assertEquals([1, 2, 3, 4], $merged->getIterations());
            }
        ];

        yield [
            function (SubjectMetadata $subject1, SubjectMetadata $subject2): void {
                $subject1->setRevs([1, 2]);
                $subject2->setRevs([3, 4]);
            },
            function (SubjectMetadata $merged): void {
                self::assertEquals([1, 2, 3, 4], $merged->getRevs());
            }
        ];

        yield [
            function (SubjectMetadata $subject1, SubjectMetadata $subject2): void {
                $subject1->setSkip(false);
                $subject2->setSkip(true);
            },
            function (SubjectMetadata $merged): void {
                self::assertTrue($merged->getSkip());
            }
        ];

        yield [
            function (SubjectMetadata $subject1, SubjectMetadata $subject2): void {
                $subject1->setSleep(10);
                $subject2->setSleep(20);
            },
            function (SubjectMetadata $merged): void {
                self::assertEquals(20, $merged->getSleep());
            }
        ];

        yield [
            function (SubjectMetadata $subject1, SubjectMetadata $subject2): void {
                $subject1->setOutputTimeUnit('ms');
                $subject2->setOutputTimeUnit('s');
            },
            function (SubjectMetadata $merged): void {
                self::assertEquals('s', $merged->getOutputTimeUnit());
            }
        ];

        yield [
            function (SubjectMetadata $subject1, SubjectMetadata $subject2): void {
                $subject1->setOutputTimePrecision(2);
                $subject2->setOutputTimePrecision(4);
            },
            function (SubjectMetadata $merged): void {
                self::assertEquals(4, $merged->getOutputTimePrecision());
            }
        ];

        yield [
            function (SubjectMetadata $subject1, SubjectMetadata $subject2): void {
                $subject1->setOutputMode('throughput');
                $subject2->setOutputMode('asd');
            },
            function (SubjectMetadata $merged): void {
                self::assertEquals('asd', $merged->getOutputMode());
            }
        ];

        yield [
            function (SubjectMetadata $subject1, SubjectMetadata $subject2): void {
                $subject1->setWarmup([12]);
                $subject2->setWarmup([13]);
            },
            function (SubjectMetadata $merged): void {
                self::assertEquals([12,13], $merged->getWarmup());
            }
        ];

        yield [
            function (SubjectMetadata $subject1, SubjectMetadata $subject2): void {
                $subject1->setAssertions(['1']);
                $subject2->setAssertions(['2']);
            },
            function (SubjectMetadata $merged): void {
                self::assertEquals(['1','2'], $merged->getAssertions());
            }
        ];

        yield [
            function (SubjectMetadata $subject1, SubjectMetadata $subject2): void {
                $subject1->setExecutor(new ExecutorMetadata('foobar', ['1']));
                $subject2->setExecutor(new ExecutorMetadata('barfoo', ['2']));
            },
            function (SubjectMetadata $merged): void {
                self::assertEquals(new ExecutorMetadata('barfoo', ['2']), $merged->getExecutor());
            }
        ];

        yield [
            function (SubjectMetadata $subject1, SubjectMetadata $subject2): void {
                $subject1->setTimeout(2);
                $subject2->setTimeout(4);
            },
            function (SubjectMetadata $merged): void {
                self::assertEquals(4, $merged->getTimeout());
            }
        ];
    }
}
