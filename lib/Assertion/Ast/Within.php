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

class Within extends Operator
{
    /**
     * @var Value
     */
    private $value;

    public function __construct(Value $value)
    {
        $this->value = $value;
    }

    public function isSatisfiedBy(Parameter $parameter1, Parameter $parameter2, Arguments $arguments): bool
    {
        $leftValue = $parameter1->resolveValue($arguments);
        $rightValue = $parameter2->resolveValue($arguments);

        if ($this->value->unit()->type() === '%') {
            return $this->isSatisfiedByPercent(
                $this->value->number(),
                $leftValue,
                $rightValue
            );
        }

        return false;
    }

    /**
     * @param int|float $leftValue
     * @param int|float $rightValue
     */
    private function isSatisfiedByPercent(Number $number, $leftValue, $rightValue): bool
    {
        $diff = Statistics::percentageDifference($leftValue, $rightValue);
        $diff = (new Number($diff))->asPositive();
        return $diff->lessThanOrEqualTo($number);
    }
}


