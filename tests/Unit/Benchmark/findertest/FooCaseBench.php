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

class FooTestCaseBench
{
    public function provideNodes()
    {
        return array(
            array(
                'nb_nodes' => 10,
                'template' => 'arg',
            ),
            array(
                'nb_nodes' => 100,
                'template2' => 'arg',
            ),
            array(
                'nb_nodes' => 1000,
                'template3' => 'arg',
            ),
        );
    }

    public function provideColumns()
    {
        return array(
            array(
                'columns' => '*',
            ),
            array(
                'columns' => 'title',
            ),
            array(
                'columns' => 'title, body',
            ),
            array(
                'columns' => 'title, body, foobar, title, body, foobar',
            ),
        );
    }

    public function beforeSelectSql(BenchIteration $iteration)
    {
    }

    /**
     * @beforeMethod setupSelectSql
     * @paramProvider provideNodes
     * @paramProvider provideColumns
     * @iterations 3
     */
    public function benchSelectSql(BenchIteration $iteration)
    {
    }

    /**
     * @beforeMethod setupSelectSql
     * @paramProvider provideNodes
     * @paramProvider provideColumns
     * @iterations 3
     */
    public function benchTraverseSomething(BenchIteration $iteration)
    {
    }
}
