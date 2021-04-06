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

namespace PhpBench;

use PhpBench\Console\Application;
use PhpBench\DependencyInjection\Container;
use PhpBench\Exception\ConfigurationPreProcessingError;
use PhpBench\Extension\CoreExtension;
use PhpBench\Extension\ExpressionExtension;
use PhpBench\Extension\ReportExtension;
use PhpBench\Extension\RunnerExtension;
use PhpBench\Extension\StorageExtension;
use PhpBench\Extensions\XDebug\XDebugExtension;
use PhpBench\Json\JsonDecoder;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function set_error_handler;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use Webmozart\PathUtil\Path;

class PhpBench
{
    // PHPBench version: @git_tag@ will be replaced by box.
    const VERSION = '@git_tag@';

    // URL to phar and version file for self-updating
    const PHAR_URL = 'https://phpbench.github.io/phpbench/phpbench.phar';
    const PHAR_VERSION_URL = 'https://phpbench.github.io/phpbench/phpbench.phar.version';

    public static function run(?InputInterface $input = null, ?OutputInterface $output = null): void
    {
        $input = $input ?: new ArgvInput();
        self::registerErrorHandler();

        $container = self::loadContainer($input);
        $container->get(Application::class)->run(
            $input,
            $output ?? $container->get(CoreExtension::SERVICE_OUTPUT_ERR)
        );
    }

    public static function loadContainer(InputInterface $input): Container
    {
        $config = self::loadConfig($input);

        $extensions = array_merge([
            CoreExtension::class,
            RunnerExtension::class,
            ReportExtension::class,
            ExpressionExtension::class,
            StorageExtension::class,
        ], $config['extensions']);

        if (extension_loaded('xdebug')) {
            $extensions[] = XDebugExtension::class;
        }
        $container = new Container(array_unique($extensions), $config);
        $container->init();
        return $container;
    }

    /**
     * If the path is relative we need to use the current working path
     * because otherwise it will be the script path, which is wrong in the
     * context of a PHAR.
     *
     */
    public static function normalizePath(string $path): string
    {
        if (Path::isAbsolute($path)) {
            return $path;
        }

        return getcwd() . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * @return array<string,mixed>
     */
    private static function loadConfig(InputInterface $input): array
    {
        $configPaths = [];
        $extensions = [];
        $configOverride = [];
        $profile = null;

        if ($configFile = $input->getParameterOption(['--config'])) {
            if (!file_exists($configFile)) {
                echo sprintf('Config file "%s" does not exist', $configFile) . PHP_EOL;

                exit(1);
            }
            $configPaths = [$configFile];
        }

        if ($value = $input->getParameterOption(['--bootstrap', '-b'])) {
            $configOverride['bootstrap'] = self::getBootstrapPath(getcwd(), $value);
        }

        if ($input->getParameterOption(['--no-ansi'])) {
            $configOverride[CoreExtension::PARAM_CONSOLE_ANSI] = false;
        }

        if ($value = $input->getParameterOption(['--extension'])) {
            $extensions[] = $value;
        }

        if ($value = $input->getParameterOption(['--php-binary'])) {
            $configOverride['php_binary'] = $value;
        }

        if ($value = $input->getParameterOption(['--php-wrapper'])) {
            $configOverride['php_wrapper'] = $value;
        }

        if ($value = $input->getParameterOption(['php-config'])) {
            $jsonParser = new JsonDecoder();
            $value = $jsonParser->decode($value);
            $configOverride['php_config'] = $value;
        }

        if ($input->getParameterOption(['--php-disable-ini'])) {
            $configOverride['php_disable_ini'] = true;
        }

        if ($value = $input->getParameterOption(['profile'])) {
            $profile = $value;
        }

        if ($value = $input->getParameterOption(['theme'])) {
            $configOverride['expression.theme'] = $value;
        }

        if ($input->getParameterOption(['-vvv'])) {
            $configOverride[CoreExtension::PARAM_DEBUG] = true;
        }

        if (empty($configPaths)) {
            $configPaths = [
                getcwd() . '/phpbench.json',
                getcwd() . '/phpbench.json.dist',
            ];
        }

        $config = [
            'extensions' => [],
            'bootstrap' => null,
        ];

        foreach ($configPaths as $configPath) {
            if (!file_exists($configPath)) {
                continue;
            }

            $configRaw = (string)file_get_contents($configPath);

            try {
                $parser = new JsonParser();
                $parser->parse($configRaw);
            } catch (ParsingException $e) {
                echo 'Error parsing config file:' . PHP_EOL . PHP_EOL;
                echo $e->getMessage();

                exit(1);
            }

            $config = array_merge(
                $config,
                json_decode($configRaw, true)
            );
            $config['config_path'] = $configPath;

            if ($config['bootstrap']) {
                $config['bootstrap'] = self::getBootstrapPath(
                    dirname($configPath), $config['bootstrap']
                );
            }

            break;
        }

        $config = array_merge(
            $config,
            $configOverride
        );

        if (null !== $profile) {
            $config = self::mergeProfile($config, $profile);
        }
        unset($config['profiles']);

        // add any manually specified extensions
        foreach ($extensions as $extension) {
            $config['extensions'][] = $extension;
        }

        return $config;
    }

    private static function getBootstrapPath($configDir, $bootstrap): ?string
    {
        if (!$bootstrap) {
            return null;
        }

        // if the path is absolute, return it unmodified
        if ('/' === substr($bootstrap, 0, 1)) {
            return $bootstrap;
        }

        return $configDir . '/' . $bootstrap;
    }

    private static function mergeProfile(array $config, string $profile): array
    {
        if (!isset($config['profiles'][$profile])) {
            throw new ConfigurationPreProcessingError(sprintf(
                'Unknown profile "%s" specified, defined profiles: "%s"',
                $profile, implode('", "', array_keys($config['profiles'] ?? []))
            ));
        }

        return array_merge($config, $config['profiles'][$profile]);
    }

    private static function registerErrorHandler(): void
    {
        $input = new ArgvInput();
        $output = (new ConsoleOutput())->getErrorOutput();

        $format = new SymfonyStyle($input, $output);
        set_error_handler(function (
            int $code,
            string $message,
            string $file,
            int $line,
            ?array $context = null
        ) use ($format): ?bool {
            $format->error(sprintf(
                '%s in %s:%s',
                $message,
                $file,
                $line
            ));

            exit(255);
        }, E_USER_ERROR);

        set_exception_handler(function (Throwable $throwable) use ($format, $input): void {
            $format->text(sprintf('%s:%s', $throwable->getFile(), $throwable->getLine()));
            $format->error($throwable->getMessage());

            if ($input->hasParameterOption(['-v', '-vv', '-vvv'])) {
                $format->block($throwable->getTraceAsString());
            }

            exit(255);
        });
    }
}
