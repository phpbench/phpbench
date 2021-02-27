<?php

namespace PhpBench\Tests\Example;

use Generator;
use PhpBench\Expression\Ast\ComparisonNode;
use PhpBench\Expression\Ast\PhpValue;
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
        $expressions = explode("\n", trim(file_get_contents($filename)));
        $container = $this->container();

        foreach ($expressions as $expression) {
            (function (Lexer $lexer, Parser $parser, Evaluator $evaluator) use ($expression, $filename): void {
                $node = $parser->parse($lexer->lex($expression));
                $result = $evaluator->evaluate($node, []);

                if (!$node instanceof ComparisonNode) {
                    return;
                }

                if (!$result instanceof PhpValue) {
                    return;
                }

                self::assertTrue($result->value(), sprintf(
                    '%s: %s', basename($filename), $expression
                ));
            })(
                $container->get(Lexer::class),
                $container->get(Parser::class),
                $container->get(Evaluator::class)
            );
        }
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
