<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Token;
use PhpBench\Assertion\Tokens;
use PhpBench\Expression\Ast\DelimitedListNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Parser;
use PhpBench\Expression\PrefixParselet;

class ListParselet implements PrefixParselet
{
    public function tokenType(): string
    {
        return Token::T_LIST_START;
    }

    public function parse(Parser $parser, Tokens $tokens): Node
    {
        $tokens->chomp();
        $list = $parser->parse($tokens);
        $tokens->chomp(Token::T_LIST_END);
        if ($list instanceof DelimitedListNode) {
            return new ListNode($list->left(), $list->right());
        }

        return new ListNode($list);
    }
}
