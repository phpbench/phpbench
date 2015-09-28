<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Benchmark;

use PhpBench\Benchmark\BenchmarkBuilder;
use Prophecy\Argument;

/**
 * NOTE: This test case (and probably the code it tests) is sub-optimal and
 * could be refactored.
 */
class BenchmarkBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $teleflector;
    private $parser;
    private $determinator;
    private $builder;

    public function setUp()
    {
        $this->teleflector = $this->prophesize('PhpBench\Benchmark\Teleflector');
        $this->parser = $this->prophesize('PhpBench\Benchmark\Parser');
        $this->determinator = $this->prophesize('PhpBench\Benchmark\ClassDeterminator');

        $this->builder = new BenchmarkBuilder(
            $this->teleflector->reveal(),
            $this->parser->reveal(),
            $this->determinator->reveal()

        );
    }

    /**
     * It should build representations of benchmarks
     * It should ignore benchmark methods which do not begin wth "bench"
     * It should pass parameter sets to the subject.
     */
    public function testBuild()
    {
        $this->teleflector->getClassInfo('foo.file')->willReturn(array(
            'class' => 'MyBenchmark',
            'abstract' => false,
            'comment' => '/** @group group_one */',
            'methods' => array(
                'benchFoobar' => array(
                    'comment' => '/** @revs 1000 */',
                ),
                'benchBarFoo' => array(
                    'comment' => '/** @revs 1000 */',
                ),
                'fooFoo' => array(),
                'beforeFoo' => array(),
                'afterFoo' => array(),
            ),
        ));
        $this->parser->parseDoc('/** @group group_one */')->willReturn(array(
            'group' => array('group_one'),
        ));
        $this->parser->parseDoc('/** @revs 1000 */', array('group' => array('group_one')))->willReturn(
            $this->getSubjectMetadata(array(
                'group' => array('group_one'),
                'beforeMethod' => array('beforeFoo'),
                'paramProvider' => array('paramProvider'),
            ))
        );
        $this->teleflector->getParameterSets('foo.file', array('paramProvider'))->willReturn(array(
            'one' => 'two',
        ));

        $benchmark = $this->builder->build('foo.file');

        $this->assertEquals('foo.file', $benchmark->getPath());
        $this->assertEquals('MyBenchmark', $benchmark->getClassFqn());

        $subjects = $benchmark->getSubjects();
        $this->assertCount(2, $subjects);
        $subject = $subjects[0];
        $this->assertEquals('benchFoobar', $subject->getMethodName());
        $this->assertEquals(array(
            'one' => 'two',
        ), $subject->getParameterSets());
    }

    /**
     * It should return NULL  if the class is abstract.
     */
    public function testAbstract()
    {
        $this->teleflector->getClassInfo('foo.file')->willReturn(array(
            'abstract' => true,
        ));

        $result = $this->builder->build('foo.file');
        $this->assertNull($result);
    }

    /**
     * It should filter subjects.
     */
    public function testFilterSubjects()
    {
        $this->teleflector->getClassInfo('foo.file')->willReturn(array(
            'class' => 'MyBenchmark',
            'abstract' => false,
            'comment' => '',
            'methods' => array(
                'benchFoobar' => array(
                    'comment' => '',
                ),
                'benchBarFoo' => array(
                    'comment' => '',
                ),
            ),
        ));
        $this->parser->parseDoc('')->willReturn(array(
            'group' => array(),
        ));
        $this->parser->parseDoc('', array('group' => array()))->willReturn($this->getSubjectMetadata(array()));

        $benchmark = $this->builder->build('foo.file', array('benchFoobar'));

        $this->assertCount(1, $benchmark->getSubjects());
    }

    /**
     * It should ignore subjects with the "skip" metadata.
     */
    public function testSkip()
    {
        $this->teleflector->getClassInfo('foo.file')->willReturn(array(
            'class' => 'MyBenchmark',
            'abstract' => false,
            'comment' => '',
            'methods' => array(
                'benchFoobar' => array(
                    'comment' => 'method one',
                ),
                'benchBarFoo' => array(
                    'comment' => '',
                ),
            ),
        ));
        $this->parser->parseDoc('', Argument::type('array'))->willReturn(
            $this->getSubjectMetadata(array())
        );
        $this->parser->parseDoc('method one', Argument::type('array'))->willReturn(
            $this->getSubjectMetadata(array(
                'skip' => true,
            ))
        );
        $this->parser->parseDoc('')->willReturn(
            $this->getSubjectMetadata(array())
        );

        $benchmark = $this->builder->build('foo.file');

        $this->assertCount(1, $benchmark->getSubjects());
    }

    /**
     * It should filter groups.
     */
    public function testFilterGroups()
    {
        $this->teleflector->getClassInfo('foo.file')->willReturn(array(
            'class' => 'MyBenchmark',
            'abstract' => false,
            'comment' => '',
            'methods' => array(
                'benchFoobar' => array(
                    'comment' => '/** one */',
                ),
                'benchBarFoo' => array(
                    'comment' => '/** two */',
                ),
            ),
        ));
        $this->parser->parseDoc('')->willReturn(array(
            'group' => array(),
        ));
        $this->parser->parseDoc('/** one */', array('group' => array()))->willReturn(
            $this->getSubjectMetadata(array(
                'group' => array('one'),
            ))
        );
        $this->parser->parseDoc('/** two */', array('group' => array()))->willReturn(
            $this->getSubjectMetadata(array(
                'group' => array('two'),
            ))
        );

        $benchmark = $this->builder->build('foo.file', array(), array('one'));

        $this->assertCount(1, $benchmark->getSubjects());
    }

    /**
     * It should throw an exception if a before method does not exist.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown before method "notExistingAfterMethod" in benchmark class
     */
    public function testInvalidAfterMethod()
    {
        $this->teleflector->getClassInfo('foo.file')->willReturn(array(
            'class' => 'MyBenchmark',
            'abstract' => false,
            'comment' => '',
            'methods' => array(
                'benchFoobar' => array(
                    'comment' => '',
                ),
                'benchBarFoo' => array(
                    'comment' => '',
                ),
            ),
        ));
        $this->parser->parseDoc('')->willReturn(array(
            'group' => array(),
        ));
        $this->parser->parseDoc('', array('group' => array()))->willReturn(
            $this->getSubjectMetadata(array(
                'group' => array(),
                'beforeMethod' => array('notExistingAfterMethod'),
            ))
        );

        $this->builder->build('foo.file', array('benchFoobar'));
    }

    /**
     * It should throw an exception if a before method does not exist.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown before method "notExistingBeforeMethod" in benchmark class
     */
    public function testInvalidBeforeMethod()
    {
        $this->teleflector->getClassInfo('foo.file')->willReturn(array(
            'class' => 'MyBenchmark',
            'abstract' => false,
            'comment' => '',
            'methods' => array(
                'benchBarFoo' => array(
                    'comment' => '',
                ),
            ),
        ));
        $this->parser->parseDoc('')->willReturn(array(
            'group' => array(),
            'paramProvider' => array(),
        ));
        $this->parser->parseDoc('', array('group' => array(), 'paramProvider' => array()))->willReturn(
            $this->getSubjectMetadata(array(
                'beforeMethod' => array('notExistingBeforeMethod'),
            ))
        );

        $this->builder->build('foo.file');
    }

    private function getSubjectMetadata($metadata)
    {
        return array_merge(array(
            'group' => array(),
            'beforeMethod' => array(),
            'afterMethod' => array(),
            'paramProvider' => array(),
            'iterations' => 1,
            'revs' => array(1),
            'skip' => false,
        ), $metadata);
    }
}
