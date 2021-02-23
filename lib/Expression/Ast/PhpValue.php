<?php

namespace PhpBench\Expression\Ast;

interface PhpValue extends Node
{
    /**
     * @return mixed
     */
    public function value();
}
