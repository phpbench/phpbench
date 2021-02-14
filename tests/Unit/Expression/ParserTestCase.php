<?php

namespace PhpBench\Tests\Unit\Expression;

use Generator;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\ExpressionEvaluator;
use PhpBench\Assertion\ExpressionLexer;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Parser;
use PhpBench\Expression\ParserFactory;
use PhpBench\Tests\IntegrationTestCase;
use PhpBench\Tests\TestCase;

abstract class ParserTestCase extends IntegrationTestCase
{
    protected function parse(string $expr): Node
    {
        $container = $this->container();

        return $container->get(
            Parser::class
        )->parse($container->get(ExpressionLexer::class)->lex($expr));
    }
}

