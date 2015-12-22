<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench;

use PhpBench\DependencyInjection\Container;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;

class PhpBench
{
    const VERSION = '0.9.0-dev';

    public static function run()
    {
        $container = new Container();
        $container->configure();
        self::loadConfig($container);
        $container->build();
        $container->get('console.application')->run();
    }

    private static function loadConfig(Container $container)
    {
        global $argv;

        $configPaths = array();
        $bootstrapOverride = null;
        foreach ($argv as $arg) {
            if ($configFile = self::parseOption($arg, 'config')) {
                if (!file_exists($configFile)) {
                    echo sprintf('Config file "%s" does not exist', $configFile) . PHP_EOL;
                    exit(1);
                }
                $configPaths = array($configFile);
            }

            if ($value = self::parseOption($arg, 'bootstrap', 'b')) {
                $bootstrapOverride = $value;
            }
        }

        if (empty($configPaths)) {
            $configPaths = array(
                getcwd() . '/phpbench.json',
                getcwd() . '/phpbench.json.dist',
            );
        }

        foreach ($configPaths as $configPath) {
            if (!file_exists($configPath)) {
                continue;
            }

            $config = file_get_contents($configPath);

            try {
                $parser = new JsonParser();
                $parser->parse($config);
            } catch (ParsingException $e) {
                echo 'Error parsing config file:' . PHP_EOL . PHP_EOL;
                echo $e->getMessage();
                exit(1);
            }

            $config = json_decode($config, true);

            $config['config_path'] = $configPath;

            if (isset($config['bootstrap'])) {
                $config['bootstrap'] = self::getBootstrapPath(
                    dirname($configPath), $config['bootstrap']
                );
            }

            $container->mergeParameters($config);
        }

        if ($bootstrapOverride) {
            $container->setParameter('bootstrap', self::getBootstrapPath(getcwd(), $bootstrapOverride));
        }
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

        foreach (array($longOption, $shortOption) as $option) {
            if (0 !== strpos($arg, $option)) {
                continue;
            }

            return substr($arg, strlen($option));
        }

        return;
    }
}
