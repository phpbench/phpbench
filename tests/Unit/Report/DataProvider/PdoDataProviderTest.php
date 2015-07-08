<?php

namespace PhpBench\Tests\Unit\Report\DataProvider;

use PhpBench\Result\SuiteResult;
use PhpBench\Result\BenchmarkResult;
use PhpBench\Result\SubjectResult;
use PhpBench\Result\IterationResult;
use PhpBench\Result\IterationsResult;
use PhpBench\Report\DataProvider\PdoDataProvider;

class PdoDataProviderTest extends \PHPUnit_Framework_TestCase
{
    private $provider;
    private $suite;

    public function setUp()
    {
        $connection = new \PDO('sqlite:memory:');
        $this->provider = new PdoDataProvider($connection);

        $iteration1 = new IterationResult(array(
            'revs' => 1, 
            'time' => 100,
            'memory' => 10,
            'memory_diff' => 10,
        ));
        $iterations = new IterationsResult(array($iteration1), array(
            'foo' => 'bar',
            'array' => array('one', 'two'),
            'assoc_array' => array(
                'one' => 'two',
                'three' => 'four',
            ),
        ));
        $subject = new SubjectResult('mySubject', 'My Subject\'s description', array('one', 'two'), array($iterations));
        $benchmark = new BenchmarkResult('Benchmark\Class', array($subject));
        $this->suite = new SuiteResult(array($benchmark));
    }

    public function testProvide()
    {
        $this->provider->setQueries(array(
            'SELECT * FROM suite'
        ));
        $this->provider->provide($this->suite);
    }
}
