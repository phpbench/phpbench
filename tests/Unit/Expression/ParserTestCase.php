<?php

namespace PhpBench\Tests\Unit\Expression;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Lexer;
use PhpBench\Expression\Parser;
use PhpBench\Tests\IntegrationTestCase;

abstract class ParserTestCase extends IntegrationTestCase
{
    protected function parse(string $expr): Node
    {
        $container = $this->container();

        return $container->get(
            Parser::class
        )->parse($container->get(Lexer::class)->lex($expr));
    }
}
