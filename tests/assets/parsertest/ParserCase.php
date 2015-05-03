<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PhpBench\BenchCase;
use PhpBench\BenchIteration;

class ParserCase implements BenchCase
{
    /**
     * @beforeMethod beforeSelectSql
     * @paramProvider provideNodes
     * @paramProvider provideColumns
     * @iterations 3
     * @description Run a select query
     */
    public function benchSelectSql(BenchIteration $iteration)
    {
    }

    /**
     * @beforeMethod setupSelectSql
     * @paramProvider provideNodes
     * @paramProvider provideColumns
     * @iterations 3
     * @description Run a select query
     */
    public function benchTraverseSomething(BenchIteration $iteration)
    {
    }
}
