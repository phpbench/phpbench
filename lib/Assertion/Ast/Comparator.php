<?php

namespace PhpBench\Assertion\Ast;

final class Comparator extends Operator
{
    /**
     * @var string
     */
    private $comparator;

    public function __construct(string $comparator)
    {
        $this->comparator = $comparator;
    }

    public function isSatisfiedBy(Parameter $parameter1, Parameter $parameter2, Arguments $arguments): bool
    {
        return false;
    }
}
