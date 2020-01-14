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

use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PHPUnit\Framework\TestCase;

class SubjectMetadataTest extends TestCase
{
    private $subject;
    private $benchmark;

    protected function setUp(): void
    {
        $this->benchmark = $this->prophesize(BenchmarkMetadata::class);
        $this->subject = new SubjectMetadata($this->benchmark->reveal(), 'subjectOne', 0);
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
}
