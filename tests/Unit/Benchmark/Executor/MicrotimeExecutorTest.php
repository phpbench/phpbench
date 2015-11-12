<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Benchmark\Executor;

use PhpBench\Benchmark\Executor;
use PhpBench\Benchmark\Executor\MicrotimeExecutor;
use PhpBench\Benchmark\Remote\Launcher;

class MicrotimeExecutorTest extends \PHPUnit_Framework_TestCase
{
    private $executor;
    private $beforeMethodFile;
    private $afterMethodFile;
    private $revFile;
    private $paramFile;
    private $paramBeforeFile;
    private $paramAfterFile;

    public function setUp()
    {
        $this->beforeMethodFile = __DIR__ . '/microtimetest/before_method.tmp';
        $this->afterMethodFile = __DIR__ . '/microtimetest/after_method.tmp';
        $this->revFile = __DIR__ . '/microtimetest/revs.tmp';
        $this->setupFile = __DIR__ . '/microtimetest/setup.tmp';
        $this->paramFile = __DIR__ . '/microtimetest/param.tmp';
        $this->paramBeforeFile = __DIR__ . '/microtimetest/parambefore.tmp';
        $this->paramAfterFile = __DIR__ . '/microtimetest/paramafter.tmp';
        $this->teardownFile = __DIR__ . '/microtimetest/teardown.tmp';

        $this->subject = $this->prophesize('PhpBench\Benchmark\Metadata\SubjectMetadata');
        $this->benchmark = $this->prophesize('PhpBench\Benchmark\Metadata\BenchmarkMetadata');

        $launcher = new Launcher(null, null);
        $this->executor = new MicrotimeExecutor($launcher);
        $this->removeTemporaryFiles();

        $this->benchmark->getPath()->willReturn(__DIR__ . '/microtimetest/ExecutorBench.php');
        $this->benchmark->getClass()->willReturn('PhpBench\Tests\Unit\Benchmark\Executor\microtimetest\ExecutorBench');
        $this->iteration = $this->prophesize('PhpBench\Benchmark\Iteration');
        $this->subject->getBenchmarkMetadata()->willReturn($this->benchmark->reveal());
    }

    public function tearDown()
    {
        $this->removeTemporaryFiles();
    }

    private function removeTemporaryFiles()
    {
        foreach (array(
            $this->beforeMethodFile,
            $this->afterMethodFile,
            $this->revFile,
            $this->setupFile,
            $this->teardownFile,
            $this->paramFile,
            $this->paramBeforeFile,
            $this->paramAfterFile,
        ) as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    /**
     * It should create a script which benchmarks the code and returns
     * the time taken and the memory used.
     */
    public function testExecute()
    {
        $this->subject->getBeforeMethods()->willReturn(array());
        $this->subject->getAfterMethods()->willReturn(array());
        $this->subject->getName()->willReturn('doSomething');
        $this->subject->getArguments()->willReturn(array());
        $this->iteration->getSubject()->willReturn($this->subject);
        $this->iteration->getRevolutions()->willReturn(10);
        $this->iteration->getParameters()->willReturn(array());

        $result = $this->executor->execute($this->iteration->reveal());

        $this->assertInstanceOf('PhpBench\Benchmark\IterationResult', $result);
        $this->assertInternalType('int', $result->getTime());
        $this->assertInternalType('int', $result->getMemory());
        $this->assertFalse(file_exists($this->beforeMethodFile));
        $this->assertFalse(file_exists($this->afterMethodFile));
        $this->assertTrue(file_exists($this->revFile));
        $this->assertEquals('10', file_get_contents($this->revFile));
    }

    /**
     * It should execute methods before the benchmark subject.
     */
    public function testExecuteBefore()
    {
        $this->subject->getBeforeMethods()->willReturn(array('beforeMethod'));
        $this->subject->getAfterMethods()->willReturn(array());
        $this->subject->getName()->willReturn('doSomething');
        $this->subject->getArguments()->willReturn(array());
        $this->iteration->getSubject()->willReturn($this->subject);
        $this->iteration->getRevolutions()->willReturn(1);
        $this->iteration->getParameters()->willReturn(array());

        $this->executor->execute($this->iteration->reveal());

        $this->assertTrue(file_exists($this->beforeMethodFile));
    }

    /**
     * It should execute methods after the benchmark subject.
     */
    public function testExecuteAfter()
    {
        $this->subject->getBeforeMethods()->willReturn(array());
        $this->subject->getAfterMethods()->willReturn(array('afterMethod'));
        $this->subject->getName()->willReturn('doSomething');
        $this->subject->getArguments()->willReturn(array());
        $this->iteration->getSubject()->willReturn($this->subject);
        $this->iteration->getRevolutions()->willReturn(1);
        $this->iteration->getParameters()->willReturn(array());

        $this->executor->execute($this->iteration->reveal());

        $this->assertTrue(file_exists($this->afterMethodFile));
    }

    /**
     * It should pass parameters to the benchmark method.
     */
    public function testParameters()
    {
        $this->subject->getBeforeMethods()->willReturn(array());
        $this->subject->getAfterMethods()->willReturn(array());
        $this->subject->getName()->willReturn('parameterized');
        $this->subject->getArguments()->willReturn(array('one', 'three'));

        $this->iteration->getSubject()->willReturn($this->subject);
        $this->iteration->getRevolutions()->willReturn(1);
        $this->iteration->getParameters()->willReturn(array(
            'one' => 'two',
            'three' => 'four',
        ));

        $this->executor->execute($this->iteration->reveal());
        $this->assertTrue(file_exists($this->paramFile));
        $params = json_decode(file_get_contents($this->paramFile), true);
        $this->assertEquals(array(
            'two',
            'four',
        ), $params);
    }

    /**
     * It should pass parameters to the before subject and after subject methods.
     */
    public function testParametersBeforeSubject()
    {
        $expected = array(
            'two',
            'four',
        );

        $this->subject->getBeforeMethods()->willReturn(array('parameterizedBefore'));
        $this->subject->getAfterMethods()->willReturn(array('parameterizedAfter'));
        $this->subject->getName()->willReturn('parameterized');
        $this->subject->getArguments()->willReturn(array('one', 'three'));

        $this->iteration->getSubject()->willReturn($this->subject);
        $this->iteration->getRevolutions()->willReturn(1);
        $this->iteration->getParameters()->willReturn($expected);

        $this->executor->execute($this->iteration->reveal());

        $this->assertTrue(file_exists($this->paramBeforeFile));
        $params = json_decode(file_get_contents($this->paramBeforeFile), true);
        $this->assertEquals($expected, $params);

        $this->assertTrue(file_exists($this->paramAfterFile));
        $params = json_decode(file_get_contents($this->paramAfterFile), true);
        $this->assertEquals($expected, $params);
    }

    /**
     * It should map non-associative parameters to arguments
     */
    public function testAssocArgs()
    {
        $this->subject->getBeforeMethods()->willReturn(array());
        $this->subject->getAfterMethods()->willReturn(array());
        $this->subject->getName()->willReturn('parameterized');
        $this->subject->getArguments()->willReturn(array('hello', 'goodbye'));

        $this->iteration->getSubject()->willReturn($this->subject);
        $this->iteration->getRevolutions()->willReturn(1);
        $this->iteration->getParameters()->willReturn(array(
            'two',
            'four',
        ));

        $this->executor->execute($this->iteration->reveal());
        $this->assertTrue(file_exists($this->paramFile));
        $params = json_decode(file_get_contents($this->paramFile), true);
        $this->assertEquals(array(
            'two',
            'four',
        ), $params);
    }

    /**
     * It should throw an exception if the parameters mix associative and numerical indexes.
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage Cannot mix numeric and string
     */
    public function testMixedParameterKeyTypes()
    {
        $this->subject->getArguments()->willReturn(array('hello', 'goodbye'));
        $this->subject->getName()->willReturn('doSomething');
        $this->iteration->getSubject()->willReturn($this->subject);
        $this->iteration->getParameters()->willReturn(array(
            'string' => 'two',
            'four',
        ));

        $this->executor->execute($this->iteration->reveal());
    }

    /**
     * It should throw an exception if the argument names for the subject do not match the array keys of an associative array based parameter set.
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage Should be: "arg1", "arg2", current argument names: "hello", "goodbye"
     */
    public function testNonMatchingArguments()
    {
        $this->subject->getArguments()->willReturn(array('hello', 'goodbye'));
        $this->subject->getName()->willReturn('doSomething');
        $this->iteration->getSubject()->willReturn($this->subject);
        $this->iteration->getParameters()->willReturn(array(
            'arg1' => 'two',
            'arg2' => 'four',
        ));

        $this->executor->execute($this->iteration->reveal());
    }

    /**
     * It should throw an exception if the benchmark subject expects non-provided parameters
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage Subject "doSomething" expects parameters: "arg1", "arg3", but did not get them.
     */
    public function testNonProvidedParameters()
    {
        $this->subject->getArguments()->willReturn(array('arg1', 'arg2', 'arg3'));
        $this->subject->getName()->willReturn('doSomething');
        $this->iteration->getSubject()->willReturn($this->subject);
        $this->iteration->getParameters()->willReturn(array(
            'arg2' => 'four',
        ));

        $this->executor->execute($this->iteration->reveal());
    }
}
