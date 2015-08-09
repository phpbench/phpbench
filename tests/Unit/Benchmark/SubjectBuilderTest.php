<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Benchmark;

use PhpBench\Benchmark\SubjectBuilder;

require_once __DIR__ . '/subject-builder-test/SubjectBuilderCase.php';
require_once __DIR__ . '/subject-builder-test/SubjectBuilderCaseInvalidParamProvider.php';

class SubjectBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->subjectBuilder = new SubjectBuilder();
    }

    /**
     * It should parse all of the bench methods and return anarray of
     * BenchSubject instances.
     */
    public function testBuild()
    {
        $bench = new \SubjectBuilderCase();
        $subjects = $this->subjectBuilder->buildSubjects($bench);
        $this->assertContainsOnlyInstancesOf('PhpBench\\Benchmark\\Subject', $subjects);
        $this->assertCount(2, $subjects);
        $subject = reset($subjects);

        $this->assertEquals(array('group1'), $subject->getGroups());
        $this->assertEquals(array('beforeSelectSql'), $subject->getBeforeMethods());
        $this->assertEquals(array('provideNumbers'), $subject->getParamProviders());
        $this->assertEquals(3, $subject->getNbIterations());
        $this->assertInternalType('int', $subject->getNbIterations());
    }
}
