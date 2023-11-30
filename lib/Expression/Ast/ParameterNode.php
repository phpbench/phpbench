<?php

namespace PhpBench\Expression\Ast;

final class ParameterNode extends Node
{
    public function __construct(
        /**
         * @var Node[]
         */
        private readonly array $segments
    ) {
    }

    /**
     * @return Node[]
     */
    public function segments(): array
    {
        return $this->segments;
    }
}
