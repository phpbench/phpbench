<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;
use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Expression\Ast\DelimitedListNode;
use PhpBench\Expression\AbstractEvaluator;
use PhpBench\Expression\InfixParselet;
use PhpBench\Expression\Parser;

final class ArgumentListParselet
{
    public function parse(Parser $parser, Node $left, Tokens $tokens): Node
    {
        $tokens->chomp();
        return new ArgumentListNode($left, $parser->parse($tokens));
    }
}
