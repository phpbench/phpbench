<?php

namespace PhpBench\Tests\Unit\Report\Generator;

use PhpBench\DependencyInjection\Container;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\ExpressionLanguage;
use PhpBench\Expression\Printer\EvaluatingPrinter;
use PhpBench\Report\Generator\BareGenerator;
use PhpBench\Report\Generator\ExpressionGenerator;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Report\Transform\SuiteCollectionTransformer;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;

class BareGeneratorTest extends GeneratorTestCase
{
    protected function acceptanceSubPath(): string
    {
        return 'bare';
    }

    protected function createGenerator(Container $container): GeneratorInterface
    {
        return new BareGenerator(
            new SuiteCollectionTransformer()
        );
    }
}
