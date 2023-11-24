<?php

namespace PhpBench\Tests\Unit\Report\Generator;

use PhpBench\DependencyInjection\Container;
use PhpBench\Expression\ExpressionEvaluator;
use PhpBench\Report\Generator\ExpressionGenerator;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Report\Transform\SuiteCollectionTransformer;
use PhpBench\Tests\Util\Approval;
use PhpBench\Tests\Util\TestUtil;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;

class ExpressionGeneratorTest extends GeneratorTestCase
{
    protected static function acceptanceSubPath(): string
    {
        return 'expression';
    }

    protected function createGenerator(Container $container): GeneratorInterface
    {
        return new ExpressionGenerator(
            $container->get(ExpressionEvaluator::class),
            new SuiteCollectionTransformer(),
            new ConsoleLogger(new ConsoleOutput())
        );
    }

    public function testOnlyUsesFirstSuite(): void
    {
        $generator = $this->createGenerator($this->container());
        $result = $this->generate(TestUtil::createCollection([
            [
                'name' => 'one',
                "subjects" => [
                    "one"
                ],
                'baseline' => [
                    'name' => 'base',
                    "subjects" => [
                        "one"
                    ],
                    "iterations" => [
                        20
                    ]
                ]
            ],
            ['name' => 'two'],
            ['name' => 'three'],
            ['name' => 'four'],
        ]), []);
        $approval = Approval::create(__DIR__ .'/expression-special/multiple_suites', 0);
        $approval->approve($result);
    }
}
