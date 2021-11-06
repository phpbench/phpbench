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
use PhpBench\Tests\TestCase;

class BenchmarkMetadataTest extends TestCase
{
    /**
     * @var BenchmarkMetadata
     */
    private $metadata;

    /**
     * @var SubjectMetadata
     */
    private $subject1;

    /**
     * @var SubjectMetadata
     */
    private $subject2;

    protected function setUp(): void
    {
        $this->metadata = new BenchmarkMetadata('/path/to', 'Class');
        $this->subject1 = $this->metadata->getOrCreateSubject('subjectOne');
        $this->subject2 = $this->metadata->getOrCreateSubject('subjectTwo');
    }

    /**
     * It should filter subjects based on a given filter.
     */
    public function testFilter(): void
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
    public function testFilterClassName(): void
    {
        $this->metadata->filterSubjectNames(['Class::subjectOne*']);
        $this->assertCount(1, $this->metadata->getSubjects());
    }

    /**
     * It should filter using a regex.
     */
    public function testFilterRegex(): void
    {
        $this->metadata->filterSubjectNames(['.*One*']);
        $this->assertCount(1, $this->metadata->getSubjects());
    }

    /**
     * It should say if it has subjects or not.
     *
     * @depends testFilter
     */
    public function testHasSubjects(): void
    {
        $this->assertTrue($this->metadata->hasSubjects());
        $this->metadata->filterSubjectNames(['subjectSeventySeven']);
        $this->assertFalse($this->metadata->hasSubjects());
    }

    public function testMerge(): void
    {
        $benchmark1 = new BenchmarkMetadata(__FILE__, __CLASS__);
        $benchmark2 = new BenchmarkMetadata(__FILE__, __CLASS__);

        $benchmark1->setBeforeClassMethods(['one', 'two']);
        $benchmark2->setBeforeClassMethods(['three', 'four']);
        $benchmark1->setAfterClassMethods(['1', '3']);
        $benchmark2->setAfterClassMethods(['2', '4']);

        $benchmark1->merge($benchmark2);

        self::assertEquals(['one', 'two', 'three', 'four'], $benchmark1->getBeforeClassMethods());
        self::assertEquals(['1', '3', '2', '4'], $benchmark1->getAfterClassMethods());
    }
}
