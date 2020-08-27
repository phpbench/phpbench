<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Assertion;

use PhpBench\Assertion\Ast;

final class Predicate
{
    /**
     * @var Argument
     */
    private $arg1;

    /**
     * @var Operator
     */
    private $operator;

    /**
     * @var Argument
     */
    private $arg2;

    public function __construct(Argument $arg1, Operator $operator, Argument $arg2)
    {
        $this->arg1 = $arg1;
        $this->operator = $operator;
        $this->arg2 = $arg2;
    }
}
