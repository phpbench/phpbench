<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests;

use PhpBench\Benchmark\CartesianParameterIterator;

class CartesianIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should generate the cartestian product of all sets for each iteration.
     *
     * @dataProvider provideIterate
     */
    public function testIterate($parameterSets, $expected)
    {
        $iterator = new CartesianParameterIterator($parameterSets);
        $result = array();
        foreach ($iterator as $parameters) {
            $result[] = $parameters->getArrayCopy();
        }

        $this->assertEquals($expected, $result);
    }

    public function provideIterate()
    {
        return array(
            array(
                // parameter sets
                array(
                    array(
                        array('optimized' => false),
                        array('optimized' => true),
                    ),
                    array(
                        array('nb_foos' => 4),
                        array('nb_foos' => 5),
                    ),
                ),
                // expected result
                array(
                    array(
                        'optimized' => false,
                        'nb_foos' => 4,
                    ),
                    array(
                        'optimized' => true,
                        'nb_foos' => 4,
                    ),
                    array(
                        'optimized' => false,
                        'nb_foos' => 5,
                    ),
                    array(
                        'optimized' => true,
                        'nb_foos' => 5,
                    ),
                ),
            ),
        );
    }
}
