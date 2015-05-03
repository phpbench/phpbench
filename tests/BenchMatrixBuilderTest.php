<?php

namespace PhpBench;

use PhpBench\BenchMatrixBuilder;

class BenchMatrixBuilderTest
{
    public function setUp()
    {
        $this->builder = new BenchMatrixBuilder();
    }

    /**
     * It should generate the cartestian product of all sets for each iteration
     *
     * @dataProvider provideBuild
     */
    public function testBuild($parameterSets, $expected)
    {
        $result = $this->builder->build($parameterSets);
        $this->assertEquals($expected, $result);
    }

    public function provideBuild()
    {

    }
}
