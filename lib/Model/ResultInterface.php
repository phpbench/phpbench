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

namespace PhpBench\Model;

/**
 * Marker interface for iteration results.
 *
 * Implementations should reflect a particular domain of information, e.g.
 * time, memory, xdebug..
 */
interface ResultInterface
{
    /**
     * Return a new instance based using the given array values.
     *
     * @param array $values
     *
     * @return ResultInterface
     */
    public static function fromArray(array $values);

    /**
     * Return a key value set representing the metrics in this result.
     *
     * This set will be used to serialize the results, f.e. as attributes in an
     * XML element.
     *
     * ```
     * [
     *     'stat_1' => 1234,
     *     'stat_2' => 5678,
     * ]
     * ```
     *
     * If the key of this result is "foo" then the set might be serialized as:
     *
     * ```
     * <iteration foo-stat-1="1234" foo-stat-2="5678"/>
     * ```
     *
     * @retrun array
     */
    public function getMetrics();

    /**
     * Return a short key which should represent this result, f.e. `time`.
     * This key must be unique in the set of all result classes.
     *
     * @return string
     */
    public function getKey();
}
