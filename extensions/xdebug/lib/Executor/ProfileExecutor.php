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
use PhpBench\Path\Path;
use PhpBench\Registry\Config;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileExecutor implements BenchmarkExecutorInterface
{
    /**
     * @var TemplateExecutor
     */
    private $innerExecutor;

    /**
     * @var string
     */
    private $cwd;

    /**
     * @var XDebugUtil
     */
    private $xdebugUtil;

    public function __construct(TemplateExecutor $innerExecutor, XDebugUtil $xdebugUtil, string $cwd)
    {
        $this->innerExecutor = $innerExecutor;
        $this->cwd = $cwd;
        $this->xdebugUtil = $xdebugUtil;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $this->innerExecutor->configure($options);

        $options->setDefaults([
            'callback' => function (): void {
            },
            'output_dir' => 'xdebug',
        ]);
        $options->setAllowedTypes('callback', ['Closure']);
        $options->setAllowedTypes('output_dir', ['string']);
    }

    public function execute(ExecutionContext $context, Config $config): ExecutionResults
    {
        $outputDir = $config['output_dir'];
        $callback = $config['callback'];
        $name = $this->xdebugUtil->filenameFromContext($context, '.cachegrind');

        $config[TemplateExecutor::OPTION_PHP_CONFIG] = $this->resolveXdebugIniSettings($outputDir, $name);
        $results = $this->innerExecutor->execute($context, $config);

        $callback($context);

        return $results;
    }

    /**
     * @return array<string,mixed>
     */
    private function resolveXdebugIniSettings(string $outputDir, string $name): array
    {
        $xdebugVersion = $this->xdebugUtil->discoverXdebugMajorVersion();

        $outputPath = Path::makeAbsolute($outputDir, $this->cwd);

        if ($xdebugVersion === '3') {
            return [
                'xdebug.mode' => 'profile',
                'xdebug.output_dir' => $outputPath,
                'xdebug.profiler_output_name' => $name,
            ];
        }

        return [
            'xdebug.profiler_enable' => 1,
            'xdebug.profiler_output_dir' => $outputPath,
            'xdebug.profiler_output_name' => $name,
        ];
    }
}
