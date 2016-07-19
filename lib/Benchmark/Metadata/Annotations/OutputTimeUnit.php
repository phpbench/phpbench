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

namespace PhpBench\Benchmark\Metadata\Annotations;

/**
 * @Annotation
 * @Taget({"METHOD", "CLASS"})
 * @Attributes({
 *    @Attribute("value", required = true, type="string"),
 *    @Attribute("precision", required = false, type="integer")
 * })
 */
class OutputTimeUnit
{
    private $timeUnit;
    private $precision;

    public function __construct($timeUnit)
    {
        $this->timeUnit = $timeUnit['value'];

        if (isset($timeUnit['precision'])) {
            $this->precision = $timeUnit['precision'];
        }
    }

    public function getTimeUnit()
    {
        return $this->timeUnit;
    }

    public function getPrecision()
    {
        return $this->precision;
    }
}
