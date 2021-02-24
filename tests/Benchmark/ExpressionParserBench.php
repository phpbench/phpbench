<?php

namespace PhpBench\Tests\Benchmark;

use Generator;
use PhpBench\Assertion\ExpressionParser;
use PhpBench\DependencyInjection\Container;
use PhpBench\Expression\Lexer;
use PhpBench\Expression\Parser;
use PhpBench\Extension\CoreExtension;
use PhpBench\Extension\ExpressionExtension;

/**
 * @Revs(10)
 * @Iterations(3)
 * @BeforeMethods({"setUp"})
 * @OutputTimeUnit("milliseconds")
 * @Assert("mode(variant.time.avg) < mode(baseline.time.avg) +/- 5%")
 */
class ExpressionParserBench
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Lexer
     */
    private $lexer;


    public function setUp(): void
    {
        $container = new Container([
            ExpressionExtension::class
        ]);
        $container->init();
        $this->parser = $container->get(Parser::class);
        $this->lexer = $container->get(Lexer::class);
    }

    /**
     * @ParamProviders({"provideExpressions"})
     *
     * @param array<mixed> $params
     */
    public function benchEvaluate(array $params): void
    {
        $this->parser->parse($this->lexer->lex($params['expr']));
    }

    /**
     * @return Generator<mixed>
     */
    public function provideExpressions(): Generator
    {
        yield '10 seconds < 10 seconds +/- 10 seconds' => [
            'expr' => '10 seconds < 10 seconds +/- 10 seconds',
        ];

        yield '10 seconds < 10 seconds' => [
            'expr' => '10 seconds < 10 seconds',
        ];
    }
}
