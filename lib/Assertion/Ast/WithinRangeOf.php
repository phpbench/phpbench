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

namespace PhpBench\Assertion\Ast;

use PhpBench\Assertion\Ast;

class WithinRangeOf implements Assertion
{
    /**
     * @var Ast\Value
     */
    private $value1;

    /**
     * @var Ast\Value
     */
    private $range;

    /**
     * @var Ast\Value
     */
    private $value2;

    public function __construct(
        Value $value1,
        Value $range,
        Value $value2
    ) {
        $this->value1 = $value1;
        $this->range = $range;
        $this->value2 = $value2;
    }

    public function value1(): Value
    {
        return $this->value1;
    }

    public function range(): Value
    {
        return $this->range;
    }

    public function value2(): Value
    {
        return $this->value2;
    }
}
