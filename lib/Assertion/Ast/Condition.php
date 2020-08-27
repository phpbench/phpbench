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

final class Condition
{
    /**
     * @var Parameter
     */
    private $parameter1;

    /**
     * @var Operator
     */
    private $operator;

    /**
     * @var Parameter
     */
    private $parameter2;

    public function __construct(Parameter $parameter1, Operator $operator, Parameter $parameter2)
    {
        $this->parameter1 = $parameter1;
        $this->operator = $operator;
        $this->parameter2 = $parameter2;
    }

    public function isSatisfied(Arguments $arguments): bool
    {
        return $this->operator->isSatisfiedBy($this->parameter1, $this->parameter2, $arguments);
    }
}

