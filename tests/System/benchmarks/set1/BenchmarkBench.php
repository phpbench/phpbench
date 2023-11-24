<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

class BenchmarkBench
{
    public function benchRandom(): void
    {
        usleep(rand(0, 100));
    }

    /**
     * @Iterations(1)
     *
     * @Revs(1000)
     *
     * @Groups({"do_nothing"})
     */
    public function benchDoNothing(): void
    {
    }

    /**
     * @ParamProviders({"provideParamsOne", "provideParamsTwo"})
     *
     * @Groups({"parameterized"})
     *
     * @Iterations(1)
     */
    public function benchParameterized($params): void
    {
    }

    public static function provideParamsOne()
    {
        return [
            ['length' => '1'],
            ['length' => '2'],
        ];
    }

    public static function provideParamsTwo()
    {
        return [
            ['strategy' => 'left'],
            ['strategy' => 'right'],
        ];
    }
}
