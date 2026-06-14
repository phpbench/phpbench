<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\LambdaNode;

final class UserFunction
{
    public function __construct(
        private LambdaNode $expr
    ) {
    }

    public function lambda(): LambdaNode
    {
        return $this->expr;
    }

    public function __invoke(...$args): array
    {
        return $args;
    }
}
