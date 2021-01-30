<?php

namespace PhpBench\Assertion\Ast;

interface UnitNode extends Node
{
    public function unit(): string;
}
