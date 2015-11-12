<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Benchmark\reflector;

/**
 * Some doc comment.
 */
class ExampleClass
{
    /**
     * Method One Comment.
     */
    public function methodOne($one)
    {
    }

    /**
     * Method Two Comment.
     */
    public function methodTwo($one, $two)
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
