<?php

namespace PhpBench\Tests\Unit\Report\Generator;

use PhpBench\DependencyInjection\Container;
use PhpBench\Extension\ExpressionExtension;
use PhpBench\Report\GeneratorInterface;
use function file_get_contents;
use Generator;
use function json_encode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\ExpressionLanguage;
use PhpBench\Expression\Printer\EvaluatingPrinter;
use PhpBench\Formatter\FormatRegistry;
use PhpBench\Formatter\Formatter;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Report\Generator\ExpressionGenerator;
use PhpBench\Report\Renderer\ConsoleRenderer;
use PhpBench\Tests\IntegrationTestCase;
use PhpBench\Tests\Util\TestUtil;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Throwable;

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
