<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Model;

class SuiteCollection implements \IteratorAggregate
{
    /**
     * @var Suite[]
     */
    private $suites;

    /**
     * @param Suite[] $suites
     */
    public function __construct(array $suites = array())
    {
        $this->suites = $suites;
    }

    /**
     * Return the suites.
     *
     * @return Suite[]
     */
    public function getSuites()
    {
        return $this->suites;
    }

    /**
     * Add a suite to the collection.
     *
     * @param Suite $suite
     */
    public function addSuite(Suite $suite)
    {
        $this->suites[] = $suite;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->suites);
    }
}
