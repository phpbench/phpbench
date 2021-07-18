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

namespace PhpBench\Examples\Benchmark\Micro;

function hash_algos()
{
    yield ['algo' => 'md5'];

    yield ['algo' => 'sha256'];
}

function hash_strings() {
    yield ['size' => 1000];
    yield ['size' => 100];
    yield ['size' => 10];
}

/**
 * @Revs(1000)
 * @Iterations(10)
 */
class HashingBench
{
    /**
     * @var string
     */
    private $string = 'x';

    public function setUp(array $params): void
    {
        $this->string = str_repeat('X', $params['size']);
    }

    /**
     * @Assert("mode(variant.time.avg) < 1ms")
     * @ParamProviders({"\PhpBench\Examples\Benchmark\Micro\hash_algos", "\PhpBench\Examples\Benchmark\Micro\hash_strings"})
     * @BeforeMethods("setUp")
     */
    public function benchAlgos($params): void
    {
        hash($params['algo'], $this->string);
    }
}
