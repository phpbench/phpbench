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
use PHPUnit\Framework\TestCase;

class BenchmarkMetadataTest extends TestCase
{
    private $metadata;

    protected function setUp(): void
    {
        $this->metadata = new BenchmarkMetadata('/path/to', 'Class');
        $this->subject1 = $this->metadata->getOrCreateSubject('subjectOne');
        $this->subject2 = $this->metadata->getOrCreateSubject('subjectTwo');
    }

    /**
     * It should filter subjects based on a given filter.
     */
    public function testFilter()
    {
        $this->assertCount(2, $this->metadata->getSubjects());
        $this->metadata->filterSubjectNames(['subjectOne']);
        $this->assertCount(1, $this->metadata->getSubjects());
        $this->metadata->filterSubjectNames(['subjectSeventySeven']);
        $this->assertCount(0, $this->metadata->getSubjects());
    }

    /**
     * It should filter on the class name.
     */
    public function testFilterClassName()
    {
        $this->metadata->filterSubjectNames(['Class::subjectOne*']);
        $this->assertCount(1, $this->metadata->getSubjects());
    }

    /**
     * It should filter using a regex.
     */
    public function testFilterRegex()
    {
        $this->metadata->filterSubjectNames(['.*One*']);
        $this->assertCount(1, $this->metadata->getSubjects());
    }

    /**
     * It should say if it has subjects or not.
     *
     * @depends testFilter
     */
    public function testHasSubjects()
    {
        $this->assertTrue($this->metadata->hasSubjects());
        $this->metadata->filterSubjectNames(['subjectSeventySeven']);
        $this->assertFalse($this->metadata->hasSubjects());
    }
}
