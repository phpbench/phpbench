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

namespace PhpBench\Expression\Constraint;

/**
 * Represents a logical operator (AND, OR).
 */
class Composite extends Constraint
{
    /**
     * @var string
     */
    private $operator;

    /**
     * @var Constraint
     */
    private $constraint1 = [];

    /**
     * @var Constraint
     */
    private $constraint2 = [];

    /**
     * @param string $operator
     * @param Constraint $constraint1
     * @param Constraint $constraint2
     */
    public function __construct($operator, Constraint $constraint1, Constraint $constraint2)
    {
        $this->operator = $operator;
        $this->constraint1 = $constraint1;
        $this->constraint2 = $constraint2;
    }

    /**
     * Return the operator.
     *
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Return 1st constraint.
     *
     * @return Constraint
     */
    public function getConstraint1()
    {
        return $this->constraint1;
    }

    /**
     * Return 2nd constraint.
     *
     * @return Constraint
     */
    public function getConstraint2()
    {
        return $this->constraint2;
    }
}
