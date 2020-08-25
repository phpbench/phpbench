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

use Composer\Autoload\ClassLoader;
use PhpBench\DependencyInjection\Container;
use PhpBench\Extension\CoreExtension;
use PhpBench\Extensions\XDebug\XDebugExtension;
use PhpBench\Json\JsonDecoder;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;
use Symfony\Component\Debug\ErrorHandler;
use Webmozart\PathUtil\Path;

class PhpBench
{
    // PHPBench version: @git_tag@ will be replaced by box.
    const VERSION = '@git_tag@';

    // URL to phar and version file for self-updating
    const PHAR_URL = 'https://phpbench.github.io/phpbench/phpbench.phar';
    const PHAR_VERSION_URL = 'https://phpbench.github.io/phpbench/phpbench.phar.version';

    public static function run(ClassLoader $autoloader)
    {
        // Converts warnings to exceptions
        ErrorHandler::register();

        $config = self::loadConfig();

        if (isset($config['extension_autoloader']) && $config['extension_autoloader']) {
            $autoloadFile = $config['extension_autoloader'];

            if (!file_exists($autoloadFile)) {
                throw new \InvalidArgumentException(sprintf(
                    'Could not find extension autoload file "%s"',
                    $autoloadFile
                ));
            }

            $autoloader->unregister();

            include $autoloadFile;
            $autoloader->register(true);
        }

        $extensions = $config['extensions'];
        $extensions[] = CoreExtension::class;

        if (extension_loaded('xdebug')) {
            $extensions[] = XDebugExtension::class;
        }
        unset($config['extensions']);
        $container = new Container(array_unique($extensions), $config);
        $container->init();
        $container->get('console.application')->run();
    }

    /**
     * If the path is relative we need to use the current working path
     * because otherwise it will be the script path, which is wrong in the
     * context of a PHAR.
     *
     * @param string $path
     *
     * @return string
     */
    public static function normalizePath($path)
    {
        if (Path::isAbsolute($path)) {
            return $path;
        }

        return getcwd() . DIRECTORY_SEPARATOR . $path;
    }

    private static function loadConfig()
    {
        global $argv;

        $configPaths = [];
        $extensions = [];
        $configOverride = [];

        foreach ($argv as $arg) {
            if ($configFile = self::parseOption($arg, 'config')) {
                if (!file_exists($configFile)) {
                    echo sprintf('Config file "%s" does not exist', $configFile) . PHP_EOL;

                    exit(1);
                }
                $configPaths = [$configFile];
            }

            if ($value = self::parseOption($arg, 'bootstrap', 'b')) {
                $configOverride['bootstrap'] = self::getBootstrapPath(getcwd(), $value);
            }

            if ($value = self::parseOption($arg, 'extension')) {
                $extensions[] = $value;
            }

            if ($value = self::parseOption($arg, 'php-binary')) {
                $configOverride['php_binary'] = $value;
            }

            if ($value = self::parseOption($arg, 'php-wrapper')) {
                $configOverride['php_wrapper'] = $value;
            }

            if ($value = self::parseOption($arg, 'php-config')) {
                $jsonParser = new JsonDecoder();
                $value = $jsonParser->decode($value);
                $configOverride['php_config'] = $value;
            }

            if ($arg == '--php-disable-ini') {
                $configOverride['php_disable_ini'] = true;
            }
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

            $configRaw = file_get_contents($configPath);

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

        // add any manually specified extensions
        foreach ($extensions as $extension) {
            $config['extensions'][] = $extension;
        }

        return $config;
    }

    private static function getBootstrapPath($configDir, $bootstrap)
    {
        if (!$bootstrap) {
            return;
        }

        // if the path is absolute, return it unmodified
        if ('/' === substr($bootstrap, 0, 1)) {
            return $bootstrap;
        }

        return $configDir . '/' . $bootstrap;
    }

    private static function parseOption($arg, $longName, $shortName = null)
    {
        $longOption = '--' . $longName . '=';
        $shortOption = '-' . $shortName .'=';

        foreach ([$longOption, $shortOption] as $option) {
            if (0 !== strpos($arg, $option)) {
                continue;
            }

            return substr($arg, strlen($option));
        }
    }
}
