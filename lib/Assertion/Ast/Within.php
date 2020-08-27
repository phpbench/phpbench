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
}


