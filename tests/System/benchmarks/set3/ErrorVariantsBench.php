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

namespace PhpBench\Tests\System\benchmarks\set3;

class ErrorVariantsBench
{
    public static function provideFoos()
    {
        yield [ 'one' => 'two' ];

        yield [ 'two' => 'three' ];
    }
    /**
     * @ParamProviders({"provideFoos"})
     */
    public function benchException(): void
    {
        throw new \Exception('Foobar');
    }
}
