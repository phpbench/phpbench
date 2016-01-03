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
    const VERSION = '0.9.0';

    public static function run()
    {
        $config = self::loadConfig();
        $container = new Container($config['extensions']);
        unset($config['extensions']);
        $container->configure();
        $container->mergeParameters($config);
        $container->build();
        $container->get('console.application')->run();
    }

    private static function loadConfig()
    {
        global $argv;

        $configPaths = array();
        $bootstrapOverride = null;
        $extensions = array();
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

            if ($value = self::parseOption($arg, 'extension')) {
                $extensions[] = $value;
            }
        }

        if (empty($configPaths)) {
            $configPaths = array(
                getcwd() . '/phpbench.json',
                getcwd() . '/phpbench.json.dist',
            );
        }

        $config = array(
            'extensions' => array(),
            'bootstrap' => null,
        );

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

        if ($bootstrapOverride) {
            $config['bootstrap'] = self::getBootstrapPath(getcwd(), $bootstrapOverride);
        }

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

        foreach (array($longOption, $shortOption) as $option) {
            if (0 !== strpos($arg, $option)) {
                continue;
            }

            return substr($arg, strlen($option));
        }

        return;
    }
}
