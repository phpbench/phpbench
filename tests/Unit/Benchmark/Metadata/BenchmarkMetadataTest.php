<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Benchmark\Metadata;

use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;

class BenchmarkMetadataTest extends \PHPUnit_Framework_TestCase
{
    private $metadata;

    public function setUp()
    {
        $this->metadata = new BenchmarkMetadata('/path/to', 'Class');
        $this->subject1 = new SubjectMetadata($this->metadata, 'subjectOne', 0);
        $this->subject2 = new SubjectMetadata($this->metadata, 'subjectTwo', 1);

        $this->metadata->setSubjectMetadata($this->subject1);
        $this->metadata->setSubjectMetadata($this->subject2);
    }

    /**
     * It should say if it is in a set of groups.
     */
    public function testInGroups()
    {
        $this->metadata->setGroups(array('one', 'two', 'three'));
        $result = $this->metadata->inGroups(array('five', 'two', 'six'));
        $this->assertTrue($result);

        $result = $this->metadata->inGroups(array('eight', 'seven'));
        $this->assertFalse($result);

        $result = $this->metadata->inGroups(array());
        $this->assertFalse($result);
    }

    /**
     * It should filter subjects based on a given filter.
     */
    public function testFilter()
    {
        $this->assertCount(2, $this->metadata->getSubjectMetadatas());
        $this->metadata->filterSubjectNames(array('subjectOne'));
        $this->assertCount(1, $this->metadata->getSubjectMetadatas());
        $this->metadata->filterSubjectNames(array('subjectSeventySeven'));
        $this->assertCount(0, $this->metadata->getSubjectMetadatas());
    }

    /**
     * It should filter on the class name.
     */
    public function testFilterClassName()
    {
        $this->metadata->filterSubjectNames(array('Class::subjectOne*'));
        $this->assertCount(1, $this->metadata->getSubjectMetadatas());
    }

    /**
     * It should filter using a regex.
     */
    public function testFilterRegex()
    {
        $this->metadata->filterSubjectNames(array('.*One*'));
        $this->assertCount(1, $this->metadata->getSubjectMetadatas());
    }

    /**
     * It should say if it has subjects or not.
     *
     * @depends testFilter
     */
    public function testHasSubjects()
    {
        $this->assertTrue($this->metadata->hasSubjects());
        $this->metadata->filterSubjectNames(array('subjectSeventySeven'));
        $this->assertFalse($this->metadata->hasSubjects());
    }
}
