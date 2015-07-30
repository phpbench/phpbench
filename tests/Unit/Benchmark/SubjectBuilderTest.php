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
        $case = new \SubjectBuilderCase();
        $subjects = $this->subjectBuilder->buildSubjects($case);
        $this->assertContainsOnlyInstancesOf('PhpBench\\Benchmark\\Subject', $subjects);
        $this->assertCount(2, $subjects);
        $subject = reset($subjects);

        $this->assertEquals(array('group1'), $subject->getGroups());
        $this->assertEquals(array('beforeSelectSql'), $subject->getBeforeMethods());
        $this->assertEquals(array('one', 'two'), $subject->getParameters());
        $this->assertEquals(3, $subject->getNbIterations());
        $this->assertInternalType('int', $subject->getNbIterations());
    }

    /**
     * It should throw an exception if a parameter provider does not exist.
     *
     * @expectedException PhpBench\Exception\InvalidArgumentException
     * @expectedExceptionMessage Unknown param provider "notExistingParam" for bench benchmark
     */
    public function testInvalidParamProvider()
    {
        $case = new \SubjectBuilderCaseInvalidParamProvider();
        $this->subjectBuilder->buildSubjects($case)->willReturn(array(
            $this->subject->reveal(),
        ));
        $this->subject->getNbIterations()->willReturn(1);
        $this->subject->getParameters()->willReturn(array('notExistingParam'));
        $this->subject->getProcessIsolation()->willReturn(false);

        $this->runner->runAll();
    }
}
