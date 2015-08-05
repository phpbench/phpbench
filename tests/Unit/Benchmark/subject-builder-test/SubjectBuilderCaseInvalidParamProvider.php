<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PhpBench\BenchIteration;
use PhpBench\BenchmarkInterface;

class SubjectBuilderCaseInvalidParamProvider implements BenchmarkInterface
{
    /**
     * @paramProvider notExistingParam
     */
    public function benchSelectSql(BenchIteration $iteration)
    {
    }
}
