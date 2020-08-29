<?php

namespace PhpBench\Tests\Benchmark;

use Generator;
use PhpBench\Assertion\ExpressionParser;

/**
 * @Revs(10)
 * @Iterations(3)
 * @BeforeMethods({"setUp"})
 * @OutputTimeUnit("milliseconds")
 */
class ExpresionParserBench
{
    /**
     * @var ExpressionParser
     */
    private $parser;

    public function setUp(): void
    {
        $this->parser = new ExpressionParser();
    }

    /**
     * @ParamProviders({"provideExpressions"})
     *
     * @param array<mixed> $params
     */
    public function benchEvaluate(array $params): void
    {
        $this->parser->parse($params['expr']);
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
