<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Parser;
use PhpBench\Expression\Tokens;

final class ArgumentListParselet
{
    public function parse(Parser $parser, Node $left, Tokens $tokens): Node
    {
        $tokens->chomp();

        return new ArgumentListNode($left, $parser->parseList($tokens));
    }
}
