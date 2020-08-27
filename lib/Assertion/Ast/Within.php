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
use PhpBench\Math\Statistics;
use PhpBench\Assertion\Ast\Microseconds;
use PhpBench\Assertion\Ast\TimeValue;

class Within extends Operator
{
    /**
     * @var Parameter
     */
    private $parameter;

    public function __construct(Parameter $parameter)
    {
        $this->parameter = $parameter;
    }

    public function isSatisfiedBy(Parameter $parameter1, Parameter $parameter2, Arguments $arguments): bool
    {
        $leftValue = $parameter1->resolveValue($arguments);
        $rightValue = $parameter2->resolveValue($arguments);

        if ($this->parameter instanceof PercentageValue) {
            return $this->parameter->difference($leftValue, $rightValue);
        }

        $diff = abs($rightValue - $leftValue);

        return $diff <= $this->parameter->resolveValue($arguments);
    }

    /**
     * @param int|float $leftValue
     * @param int|float $rightValue
     */
}


