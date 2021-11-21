<?php

namespace PhpBench\Executor\Parser;

use PhpBench\Executor\Parser\Ast\StageNode;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;
use RuntimeException;

class StageParser
{
    public function parse(Tokens $tokens): StageNode
    {
        return new StageNode('root', $this->parseStages($tokens));
    }

    /**
     * @return StageNode[]
     */
    private function parseStages(Tokens $tokens): array
    {
        $stages = [];
        while ($tokens->current()->type !== Token::T_EOF) {

            if ($tokens->current()->type !== Token::T_NAME) {
                return $stages;
            }

            $stages[] = $this->parseStage($tokens);
        }

        return $stages;
    }

    private function parseStage(Tokens $tokens): StageNode
    {
        $name = $tokens->chomp(Token::T_NAME);

        if ($tokens->current()->type === Token::T_OPEN_BRACE) {
            $tokens->chomp();

            $children = $this->parseStages($tokens);

            $tokens->chomp(Token::T_CLOSE_BRACE);

            return new StageNode($name->value, $children);
        }

        if ($tokens->current()->type === Token::T_SEMICOLON) {
            $tokens->chomp();
        }

        return new StageNode($name->value, []);


    }
}
