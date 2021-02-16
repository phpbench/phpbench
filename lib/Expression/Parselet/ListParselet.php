<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\DelimitedListNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Parser;
use PhpBench\Expression\PrefixParselet;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

class ListParselet implements PrefixParselet
{
    public function tokenType(): string
    {
        return Token::T_OPEN_LIST;
    }

    public function parse(Parser $parser, Tokens $tokens): Node
    {
        $tokens->chomp();
        $list = $parser->parseList($tokens);
        $tokens->chomp(Token::T_CLOSE_LIST);

        if ($list instanceof DelimitedListNode) {
            return new ListNode($list->left(), $list->right());
        }

        return new ListNode($list);
    }
}
