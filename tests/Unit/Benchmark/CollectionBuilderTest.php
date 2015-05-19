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

use Symfony\Component\Finder\Finder;
use PhpBench\Benchmark\CollectionBuilder;

class CollectionBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $finder = new Finder();
        $finder->in(__DIR__ . '/findertest');
        $this->finder = new CollectionBuilder($finder);
    }

    /**
     * It should return a collection of all found bench cases.
     * It should not instantiate abstract classes.
     */
    public function testBuildCollection()
    {
        $collection = $this->finder->buildCollection();
        $cases = $collection->getBenchmarks();

        $this->assertCount(2, $cases);
        $this->assertContainsOnlyInstancesOf('PhpBench\\Benchmark', $cases);
    }
}
