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

use PhpBench\Executor\Benchmark\TemplateExecutor;
use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\ExecutionResults;
use PhpBench\Extensions\XDebug\XDebugUtil;
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
    public function configure(OptionsResolver $options): void
    {
        $this->innerExecutor->configure($options);

        $options->setDefaults([
            'callback' => function () {
            },
            'output_dir' => 'xdebug',
        ]);
    }

    public function execute(ExecutionContext $context, Config $config): ExecutionResults
    {
        $outputDir = $config['output_dir'];
        $callback = $config['callback'];
        $name = XDebugUtil::filenameFromContext($context, '.cachegrind');

        $config[TemplateExecutor::OPTION_PHP_CONFIG] = [
            'xdebug.profiler_enable' => 1,
            'xdebug.profiler_output_dir' => PhpBench::normalizePath($outputDir),
            'xdebug.profiler_output_name' => $name,
        ];

        $results = $this->innerExecutor->execute($context, $config);

        $callback($context);

        return $results;
    }
}
