<?php

namespace PhpBench;

use Symfony\Component\Finder\Finder;

class BenchFinderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $finder = new Finder();
        $finder->in(__DIR__ . '/assets/findertest');
        $this->finder = new BenchFinder($finder);
    }

    /**
     * It should return a collection of all found bench cases
     */
    public function testBuildCollection()
    {
        $collection = $this->finder->buildCollection();
        $cases = $collection->getCases();

        $this->assertCount(2, $cases);
        $this->assertContainsOnlyInstancesOf('PhpBench\\BenchCase', $cases);
    }
}
