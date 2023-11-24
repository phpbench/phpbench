<?php

namespace PhpBench\Tests\Benchmark\Expression;

use Generator;
use PhpBench\DependencyInjection\Container;
use PhpBench\Expression\Lexer;
use PhpBench\Expression\Parser;
use PhpBench\Extension\ExpressionExtension;

/**
 * @BeforeMethods({"setUp"})
 */
class ParserBench
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
    public static function provideExpressions(): Generator
    {
        yield 'comp. w/tol' => [
            'expr' => '10 seconds < 10 seconds +/- 10 seconds',
        ];

        yield 'comp.' => [
            'expr' => '10 seconds < 10 seconds',
        ];
    }
}
