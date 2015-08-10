<?php

namespace PhpBench\Tests\Unit\Benchmark;

use Prophecy\Argument;
use PhpBench\Benchmark\BenchmarkBuilder;

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
     * It should pass parameter sets to the subject
     */
    public function testBuild()
    {
        $this->teleflector->getClassInfo('foo.file')->willReturn(array(
            'class' => 'MyBenchmark',
            'interfaces' => array('PhpBench\BenchmarkInterface'),
            'comment' => '/** @group group_one */',
            'methods' => array(
                'benchFoobar' => array(
                    'comment' => '/** @revs 1000 */'
                ),
                'benchBarFoo' => array(
                    'comment' => '/** @revs 1000 */',
                ),
                'fooFoo' => array(),
                'beforeFoo' => array(),
                'afterFoo' => array(),
            )
        ));
        $this->parser->parseDoc('/** @group group_one */')->willReturn(array(
            'group' => array('group_one'),
        ));
        $this->parser->parseDoc('/** @revs 1000 */', array('group' => array('group_one')))->willReturn(array(
            'group' => array('group_one'),
            'beforeMethod' => array('beforeFoo'),
            'afterMethod' => array(),
            'paramProvider' => array('paramProvider'),
            'iterations' => 1,
            'revs' => array(1),
        ));
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
     * It should return NULL if a class does not implement BenchmarkInterface
     */
    public function testNotImplementing()
    {
        $this->teleflector->getClassInfo('foo.file')->willReturn(array(
            'interfaces' => array('Foobar'),
        ));

        $result = $this->builder->build('foo.file');
        $this->assertNull($result);
    }

    /**
     * It should filter subjects
     */
    public function testFilterSubjects()
    {
        $this->teleflector->getClassInfo('foo.file')->willReturn(array(
            'class' => 'MyBenchmark',
            'interfaces' => array('PhpBench\BenchmarkInterface'),
            'comment' => '',
            'methods' => array(
                'benchFoobar' => array(
                    'comment' => '',
                ),
                'benchBarFoo' => array(
                    'comment' => '',
                ),
            )
        ));
        $this->parser->parseDoc('')->willReturn(array(
            'group' => array(),
        ));
        $this->parser->parseDoc('', array('group' => array()))->willReturn(array(
            'group' => array(),
            'beforeMethod' => array(),
            'afterMethod' => array(),
            'paramProvider' => array(),
            'iterations' => 1,
            'revs' => array(1),
        ));

        $benchmark = $this->builder->build('foo.file', array('benchFoobar'));

        $this->assertCount(1, $benchmark->getSubjects());
    }

    /**
     * It should filter groups
     */
    public function testFilterGroups()
    {
        $this->teleflector->getClassInfo('foo.file')->willReturn(array(
            'class' => 'MyBenchmark',
            'interfaces' => array('PhpBench\BenchmarkInterface'),
            'comment' => '',
            'methods' => array(
                'benchFoobar' => array(
                    'comment' => '/** one */',
                ),
                'benchBarFoo' => array(
                    'comment' => '/** two */',
                ),
            )
        ));
        $this->parser->parseDoc('')->willReturn(array(
            'group' => array(),
        ));
        $this->parser->parseDoc('/** one */', array('group' => array()))->willReturn(array(
            'group' => array('one'),
            'beforeMethod' => array(),
            'afterMethod' => array(),
            'paramProvider' => array(),
            'iterations' => 1,
            'revs' => array(1),
        ));
        $this->parser->parseDoc('/** two */', array('group' => array()))->willReturn(array(
            'group' => array('two'),
            'beforeMethod' => array(),
            'afterMethod' => array(),
            'paramProvider' => array(),
            'iterations' => 1,
            'revs' => array(1),
        ));

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
            'interfaces' => array('PhpBench\BenchmarkInterface'),
            'comment' => '',
            'methods' => array(
                'benchFoobar' => array(
                    'comment' => '',
                ),
                'benchBarFoo' => array(
                    'comment' => '',
                ),
            )
        ));
        $this->parser->parseDoc('')->willReturn(array(
            'group' => array(),
        ));
        $this->parser->parseDoc('', array('group' => array()))->willReturn(array(
            'group' => array(),
            'beforeMethod' => array('notExistingAfterMethod'),
            'afterMethod' => array(),
            'paramProvider' => array(),
            'iterations' => 1,
            'revs' => array(1),
        ));

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
            'interfaces' => array('PhpBench\BenchmarkInterface'),
            'comment' => '',
            'methods' => array(
                'benchFoobar' => array(
                    'comment' => '',
                ),
                'benchBarFoo' => array(
                    'comment' => '',
                ),
            )
        ));
        $this->parser->parseDoc('')->willReturn(array(
            'group' => array(),
        ));
        $this->parser->parseDoc('', array('group' => array()))->willReturn(array(
            'group' => array(),
            'beforeMethod' => array('notExistingBeforeMethod'),
            'afterMethod' => array(),
            'paramProvider' => array(),
            'iterations' => 1,
            'revs' => array(1),
        ));

        $this->builder->build('foo.file', array('benchFoobar'));
    }
}
