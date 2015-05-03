<?php

use PhpBench\BenchCase;
use PhpBench\BenchIteration;

class FooTestCase implements BenchCase
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
     * @BeforeMethod setupSelectSql
     * @DataProvider provideNodes
     * @DataProvider provideColumns
     * @Iterations 3
     * @Description Run a select query
     */
    public function benchSelectSql(BenchIteration $iteration)
    {
    }

    /**
     * @BeforeMethod setupSelectSql
     * @DataProvider provideNodes
     * @DataProvider provideColumns
     * @Iterations 3
     * @Description Run a select query
     */
    public function benchTraverseSomething(BenchIteration $iteration)
    {
    }
}
