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

namespace PhpBench\Console\Command\Handler;

use InvalidArgumentException;
use PhpBench\Benchmark\BenchmarkFinder;
use PhpBench\Benchmark\Runner;
use PhpBench\Benchmark\RunnerConfig;
use PhpBench\Model\Suite;
use PhpBench\Progress\LoggerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunnerHandler
{
    public const ARG_PATH = 'path';
    public const OPT_FILTER = 'filter';
    public const OPT_GROUP = 'group';
    public const OPT_PARAMETERS = 'parameters';
    public const OPT_ASSERT = 'assert';
    public const OPT_REVS = 'revs';
    public const OPT_PROGRESS = 'progress';
    public const OPT_BOOTSTRAP = 'bootstrap';
    public const OPT_EXECUTOR = 'executor';
    public const OPT_STOP_ON_ERROR = 'stop-on-error';
    public const OPT_PHP_BIN = 'php-binary';
    public const OPT_PHP_CONFIG = 'php-config';
    public const OPT_PHP_WRAPPER = 'php-wrapper';
    public const OPT_PHP_DISABLE_INI = 'php-disable-ini';
    public const OPT_FORMAT = 'format';
    public const OPT_VARIANT_FILTER = 'variant';

    /**
     * @var LoggerRegistry
     */
    private $loggerRegistry;

    /**
     * @var string|null
     */
    private $defaultProgress;

    /**
     * @var array<string>
     */
    private $benchPaths;

    /**
     * @var Runner
     */
    private $runner;

    /**
     * @var BenchmarkFinder
     */
    private $finder;

    public function __construct(
        Runner $runner,
        LoggerRegistry $loggerRegistry,
        BenchmarkFinder $finder,
        ?string $defaultProgress = null,
        array $benchPaths = null
    ) {
        $this->runner = $runner;
        $this->loggerRegistry = $loggerRegistry;
        $this->defaultProgress = $defaultProgress;
        $this->benchPaths = $benchPaths;
        $this->finder = $finder;
    }

    public static function configureFilters(Command $command): void
    {
        $command->addOption(self::OPT_FILTER, [], InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Include benchmark subjects matching this filter. Matched against <fg=cyan>Fullly\Qualified\BenchmarkName::benchSubjectName</>. Can be a regex. Multiple filters combined with OR');
        $command->addOption(self::OPT_VARIANT_FILTER, [], InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Include variants matching this filter. Matched against parameter set names. Can be a regex). Multiple values combined with OR');
    }

    public static function configure(Command $command): void
    {
        $command->addArgument(self::ARG_PATH, InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Path to benchmark(s)');
        $command->addOption(self::OPT_GROUP, [], InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Group to run (can be specified multiple times)');
        $command->addOption(self::OPT_PARAMETERS, null, InputOption::VALUE_REQUIRED, 'Override parameters to use in (all) benchmarks');
        $command->addOption(self::OPT_ASSERT, null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Override assertions');
        $command->addOption(self::OPT_FORMAT, null, InputOption::VALUE_REQUIRED, 'Set progress logger format');
        $command->addOption(self::OPT_REVS, null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Override number of revs (revolutions) on (all) benchmarks');
        $command->addOption(self::OPT_PROGRESS, 'l', InputOption::VALUE_REQUIRED, 'Progress logger to use');
        self::configureFilters($command);

        // command option is parsed before the container is compiled.
        $command->addOption(self::OPT_BOOTSTRAP, 'b', InputOption::VALUE_REQUIRED, 'Set or override the bootstrap file.');
        $command->addOption(self::OPT_EXECUTOR, [], InputOption::VALUE_REQUIRED, 'Executor to use', 'remote');
        $command->addOption(self::OPT_STOP_ON_ERROR, [], InputOption::VALUE_NONE, 'Stop on the first error encountered');

        // Launcher options (processed in PhpBench.php before the container is initialized).
        $command->addOption(self::OPT_PHP_BIN, null, InputOption::VALUE_REQUIRED, 'Alternative PHP binary to use');
        $command->addOption(self::OPT_PHP_CONFIG, null, InputOption::VALUE_REQUIRED, 'JSON-like object of PHP INI settings');
        $command->addOption(self::OPT_PHP_WRAPPER, null, InputOption::VALUE_REQUIRED, 'Prefix process launch command with this string');
        $command->addOption(self::OPT_PHP_DISABLE_INI, null, InputOption::VALUE_NONE, 'Do not load the PHP INI file');
    }

    public function runFromInput(InputInterface $input, OutputInterface $output, RunnerConfig $config): Suite
    {
        $default = RunnerConfig::create()
            ->withRevolutions($input->getOption(self::OPT_REVS))
            ->withExecutor($input->getOption(self::OPT_EXECUTOR))
            ->withStopOnError($input->getOption(self::OPT_STOP_ON_ERROR))
            ->withAssertions($input->getOption(self::OPT_ASSERT))
            ->withVariantFilters($input->getOption(self::OPT_VARIANT_FILTER))
            ->withFormat($input->getOption(self::OPT_FORMAT));

        $parameters = $this->getParameters($input->getOption(self::OPT_PARAMETERS));

        if (null !== $parameters) {
            $default = $default->withParameters([$parameters]);
        }

        $config = $default->merge($config);

        $progressLoggerName = $input->getOption(self::OPT_PROGRESS) ?: $this->defaultProgress;

        if ($input->hasOption('quiet') && $input->getOption('quiet')) {
            $progressLoggerName = 'none';
        }

        $progressLogger = $this->loggerRegistry->getProgressLogger($progressLoggerName);
        $this->runner->setProgressLogger($progressLogger);

        $paths = (array)($input->getArgument(self::ARG_PATH) ?: $this->benchPaths);

        if (empty($paths)) {
            throw new InvalidArgumentException(
                'You must either specify or configure a path'
            );
        }

        return $this->runner->run($this->finder->findBenchmarks(
            $paths,
            $input->getOption(self::OPT_FILTER),
            $input->getOption(self::OPT_GROUP)
        ), $config);
    }

    private function getParameters($parametersJson)
    {
        if (null === $parametersJson) {
            return;
        }

        $parameters = [];

        if ($parametersJson) {
            $parameters = json_decode($parametersJson, true);

            if (null === $parameters) {
                throw new \InvalidArgumentException(sprintf(
                    'Could not decode parameters JSON string: "%s"',
                    $parametersJson
                ));
            }
        }

        return $parameters;
    }
}
