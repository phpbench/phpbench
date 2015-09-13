<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Benchmark\teleflector;

/**
 * Some doc comment.
 */
class ExampleClass
{
    /**
     * Method One Comment.
     */
    public function methodOne()
    {
    }

    /**
     * Method Two Comment.
     */
    public function methodTwo()
    {
    }

    public function provideParamsOne()
    {
        return array(
            array(
                'one' => 'two',
                'three' => 'four',
            ),
        );
    }

    public function provideParamsTwo()
    {
        return array(
            array(
                'five' => 'six',
                'seven' => 'eight',
            ),
        );
    }
}
