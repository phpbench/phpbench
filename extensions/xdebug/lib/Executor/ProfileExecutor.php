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

namespace PhpBench\Extensions\XDebug\Executor;

use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Executor\Benchmark\TemplateExecutor;
use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Extensions\XDebug\XDebugUtil;
use PhpBench\Model\Iteration;
use PhpBench\PhpBench;
use PhpBench\Registry\Config;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileExecutor implements BenchmarkExecutorInterface
{
    /**
     * @var TemplateExecutor
     */
    private $innerExecutor;

    public function __construct(TemplateExecutor $innerExecutor)
    {
        $this->innerExecutor = $innerExecutor;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $options)
    {
        $this->innerExecutor->configure($options);

        $options->setDefaults([
            'callback' => function () {
            },
            'output_dir' => 'xdebug',
        ]);
    }

    public function execute(SubjectMetadata $subjectMetadata, Iteration $iteration, Config $config): void
    {
        $outputDir = $config['output_dir'];
        $callback = $config['callback'];
        $name = XDebugUtil::filenameFromIteration($iteration, '.cachegrind');

        $config[TemplateExecutor::OPTION_PHP_CONFIG] = [
            'xdebug.profiler_enable' => 1,
            'xdebug.profiler_output_dir' => PhpBench::normalizePath($outputDir),
            'xdebug.profiler_output_name' => $name,
        ];

        $this->innerExecutor->execute(
            $subjectMetadata, $iteration, $config
        );

        $callback($iteration);
    }
}
