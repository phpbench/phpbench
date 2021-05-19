<?php

namespace PhpBench\Expression\Ast;

abstract class PhpValue extends Node
{
    /**
     * @return mixed
     */
    abstract public function value();
}
