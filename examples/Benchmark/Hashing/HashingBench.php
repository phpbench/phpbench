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

namespace PhpBench\Examples\Benchmark;

// section: all
/**
 * @BeforeMethods("setUp")
 */
class HashingBench
{
    /**
     * @var string
     */
    private $string = '';

    public function setUp(array $params): void
    {
        $this->string = str_repeat('X', $params['size']);
    }

    /**
     * @ParamProviders({
     *     "provideAlgos",
     *     "provideStringSize"
     * })
     */
    public function benchAlgos($params): void
    {
        hash($params['algo'], $this->string);
    }

    public function provideAlgos()
    {
        foreach (array_slice(\hash_algos(), 0, 20) as $algo) {
            if ($algo === 'md2') { // md2 is in a different performance category
                continue;
            }
            yield ['algo' => $algo];
        }

    }

    public function provideStringSize() {
        yield ['size' => 10];
        yield ['size' => 100];
        yield ['size' => 1000];
    }

}
// endsection: all
