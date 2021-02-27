<?php

namespace PhpBench\Tests\Example;

use Generator;
use PhpBench\DependencyInjection\Container;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Lexer;
use PhpBench\Expression\Parser;
use PhpBench\Tests\IntegrationTestCase;

class ExpressionLinterTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideExpression
     */
    public function testExpression(string $filename): void
    {
        /** @phpstan-ignore-next-line */
        $expression = trim(file_get_contents($filename));
        (function (Container $container) use ($expression) {
            $lexer= $container->get(Lexer::class);
            $parser = $container->get(Parser::class);
            $evaluator = $container->get(Evaluator::class);
            $evaluator->evaluate($parser->parse($lexer->lex($expression)), []);
        })($this->container());
        $this->addToAssertionCount(1);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideExpression(): Generator
    {
        /** @phpstan-ignore-next-line */
        foreach (glob(__DIR__ . '/../../examples/Expression/*') as $file) {
            yield [
                $file,
            ];
        }
    }
}
