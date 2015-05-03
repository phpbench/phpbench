<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench;

require_once __DIR__ . '/assets/parsertest/ParserCase.php';
require_once __DIR__ . '/assets/parsertest/ParserCaseInvalidAnnotation.php';

class BenchSubjectBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->parser = new BenchSubjectBuilder();
    }

    /**
     * It should parse all of the bench methods and return anarray of
     * BenchSubject instances.
     */
    public function testBuild()
    {
        $case = new \ParserCase();
        $subjects = $this->parser->buildSubjects($case);
        $this->assertContainsOnlyInstancesOf('PhpBench\\BenchSubject', $subjects);
        $this->assertCount(2, $subjects);
        $subject = reset($subjects);

        $this->assertEquals(array('beforeSelectSql'), $subject->getBeforeMethods());
        $this->assertEquals(array('provideNodes', 'provideColumns'), $subject->getParamProviders());
        $this->assertEquals(3, $subject->getNbIterations());
        $this->assertInternalType('int', $subject->getNbIterations());
        $this->assertEquals('Run a select query', $subject->getDescription());
    }
}
