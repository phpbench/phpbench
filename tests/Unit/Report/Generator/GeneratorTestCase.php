<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Tests\Unit\Report\Generator;

use function file_get_contents;
use Generator;
use function json_encode;
use PhpBench\DependencyInjection\Container;
use PhpBench\Extension\ExpressionExtension;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Report\Renderer\ConsoleRenderer;
use PhpBench\Tests\IntegrationTestCase;
use PhpBench\Tests\Util\TestUtil;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Throwable;

abstract class GeneratorTestCase extends IntegrationTestCase
{
    protected const UPDATE = false;

    /**
     * @dataProvider provideGenerate
     */
    public function testGenerate(string $path): void
    {
        $container = $this->container();
        $generator = $this->createGenerator($container);
        $options = new OptionsResolver();
        $generator->configure($options);

        try {
            $document = $generator->generate(
                new SuiteCollection([TestUtil::createSuite(array_merge([
                    'output_time_precision' => 3,
                ], $suite))]),
                new Config('asd', $options->resolve($config))
            );
            $output = new BufferedOutput();
            (
                new ConsoleRenderer($output, $container->get(ExpressionExtension::SERVICE_PLAIN_PRINTER))
            )->render($document, new Config('asd', [
                'table_style' => 'default',
            ]));
            $actual = $output->fetch();
        } catch (Throwable $e) {
            $actual = $e->getMessage();
        }

        $parts = array_values(array_filter(explode('---', file_get_contents($path), 3)));
        $suite = json_decode($parts[0], true);
        $config = json_decode($parts[1], true);
        $expected = $parts[2] ?? null;


        /** @phpstan-ignore-next-line */
        if (self::UPDATE || null === $expected) {
            file_put_contents($path, implode("\n---\n", [
                json_encode($suite, JSON_PRETTY_PRINT),
                json_encode($config, JSON_PRETTY_PRINT),
                $actual
            ]));
            $this->markTestSkipped('Generated expectation');
            /** @phpstan-ignore-next-line */
            return;
        }

        self::assertEquals(trim($expected), trim($actual), json_encode($config));
    }

    /**
     * @return Generator<mixed>
     */
    public function provideGenerate(): Generator
    {
        foreach (glob(sprintf('%s/%s/*', __DIR__, $this->acceptanceSubPath())) as $path) {
            yield [
                $path
            ];
        }
    }

    abstract protected function acceptanceSubPath(): string;

    abstract protected function createGenerator(Container $container): GeneratorInterface;
}
