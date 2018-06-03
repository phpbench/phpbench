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

namespace PhpBench\Benchmarks\Micro;

function hash_algos()
{
    yield ['algo' => 'md5'];
    yield ['algo' => 'sha256'];
}

/**
 * @Revs(1000)
 * @Iterations(10)
 */
class HashingBench
{
    /**
     * @ParamProviders({"\PhpBench\Benchmarks\Micro\hash_algos"})
     */
    public function benchAlgos($params)
    {
        return hash($params['algo'], 'Hello World');
    }
}
