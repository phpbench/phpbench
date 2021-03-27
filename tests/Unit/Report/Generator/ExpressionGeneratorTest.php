<?php

namespace PhpBench\Tests\Unit\Report\Generator;

use PhpBench\DependencyInjection\Container;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\ExpressionLanguage;
use PhpBench\Expression\Printer\EvaluatingPrinter;
use PhpBench\Report\Generator\ExpressionGenerator;
use PhpBench\Report\GeneratorInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;

class ExpressionGeneratorTest extends GeneratorTestCase
{
    protected function acceptanceSubPath(): string
    {
        return 'expression';
    }

    protected function createGenerator(Container $container): GeneratorInterface
    {
        return new ExpressionGenerator(
            $container->get(ExpressionLanguage::class),
            $container->get(Evaluator::class),
            $container->get(EvaluatingPrinter::class),
            new ConsoleLogger(new ConsoleOutput())
        );
    }
}
