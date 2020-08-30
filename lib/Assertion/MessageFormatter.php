<?php

namespace PhpBench\Assertion;

use PhpBench\Assertion\Ast\Node;

interface MessageFormatter
{
    public function format(Node $node): string;
}
