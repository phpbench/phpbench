<?php

namespace PhpBench\Tests\Unit\Benchmark;

use Prophecy\Argument;
use PhpBench\Benchmark\BenchmarkBuilder;

class BenchmarkBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $telespector;
    private $parser;
    private $determinator;
    private $builder;

    public function setUp()
    {
        $this->telespector = $this->prophesize('PhpBench\Benchmark\Telespector');
        $this->parser = $this->prophesize('PhpBench\Benchmark\Parser');
        $this->determinator = $this->prophesize('PhpBench\Benchmark\ClassDeterminator');

        $this->builder = new BenchmarkBuilder(
            $this->telespector->reveal(),
            $this->parser->reveal(),
            $this->determinator->reveal()

        );
    }

    /**
     * It should build representations of benchmarks
     * It should ignore benchmark methods which do not begin wth "bench"
     */
    public function testBuild()
    {
        $this->determinator->getClassNameFromFile('foo.file')->willReturn('MyBenchmark');
        $this->telespector->execute(Argument::type('string'), array(
            'file' => 'foo.file',
            'class' => 'MyBenchmark'
        ))->willReturn(array(
            'interfaces' => array('PhpBench\BenchmarkInterface'),
            'comment' => '/** @group group_one */',
            'methods' => array(
                'benchFoobar' => array(
                    'comment' => '/** @revs 1000 */'
                ),
                'benchBarFoo' => array(
                    'comment' => '/** @revs 1000 */'
                ),
                'fooFoo' => array(),
            )
        ));
        $this->parser->parseDoc('/** @group group_one */')->willReturn(array(
            'group' => array('group_one'),
        ));
        $this->parser->parseDoc('/** @revs 1000 */', array('group' => array('group_one')))->willReturn(array(
            'group' => array('group_one'),
            'beforeMethod' => array(),
            'afterMethod' => array(),
            'paramProvider' => array(),
            'iterations' => 1,
            'revs' => array(1),
        ));


        $benchmark = $this->builder->build('foo.file');

        $this->assertEquals('foo.file', $benchmark->getPath());
        $this->assertEquals('MyBenchmark', $benchmark->getClassFqn());

        $subjects = $benchmark->getSubjects();
        $this->assertCount(2, $subjects);
        $subject = $subjects[0];
        $this->assertEquals('benchFoobar', $subject->getMethodName());
    }

    /**
     * It should return NULL if a class does not implement BenchmarkInterface
     */
    public function testNotImplementing()
    {
        $this->determinator->getClassNameFromFile('foo.file')->willReturn('MyBenchmark');
        $this->telespector->execute(Argument::type('string'), array(
            'file' => 'foo.file',
            'class' => 'MyBenchmark'
        ))->willReturn(array(
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
        $this->determinator->getClassNameFromFile('foo.file')->willReturn('MyBenchmark');
        $this->telespector->execute(Argument::type('string'), array(
            'file' => 'foo.file',
            'class' => 'MyBenchmark'
        ))->willReturn(array(
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
        $this->determinator->getClassNameFromFile('foo.file')->willReturn('MyBenchmark');
        $this->telespector->execute(Argument::type('string'), array(
            'file' => 'foo.file',
            'class' => 'MyBenchmark'
        ))->willReturn(array(
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
}
