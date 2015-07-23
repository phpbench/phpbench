<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__ . '/../lib/Container.php';

use PhpBench\Container;

$configPaths = array();
$container = new Container();

foreach ($argv as $arg) {
    if (0 === strpos($arg, '--config=')) {
        $configFile = substr($arg, 9);
        if (!file_exists($configFile)) {
            echo sprintf('Config file "%s" does not exist', $configFile) . PHP_EOL;
            exit(1);
        }
        $configPaths = array($configFile);
    }
}

if (empty($configPaths)) {
    $configPaths = array(
        getcwd() . '/phpbench.json',
        getcwd() . '/phpbench.dist.json',
    );
}

$hasBootstrap = false;
$config = array();
foreach ($configPaths as $configPath) {
    if (file_exists($configPath)) {
        $configDir = dirname($configPath);
        $config = json_decode(file_get_contents($configPath), true);

        if (null === $config) {
            echo sprintf('Could not decode configuration file into JSON "%s"',
                $configPath
            );
            exit(1);
        }

        if (isset($config['path'])) {
            // prepend config dir to path if it is non-relative
            if (substr($config['path'], 0, 1) !== '/') {
                $config['path'] = $configDir . DIRECTORY_SEPARATOR . $config['path'];
            }
        }

        $config['config_path'] = $configPath;

        if (isset($config['bootstrap'])) {
            $bootstrap = realpath($configDir . DIRECTORY_SEPARATOR . $config['bootstrap']);
            if (!file_exists($bootstrap)) {
                echo sprintf('Bootstrap file "%s" was not found',
                    $bootstrap
                );
                exit(1);
            }
            require_once($bootstrap);
            $hasBootstrap = true;
        }
        break;
    }
}

if (false === $hasBootstrap) {
    $bootstrapPath = getcwd() . '/vendor/autoload.php';

    if (!file_exists($bootstrapPath)) {
        echo sprintf('Autoload file "%s" does not exist. Maybe you need to do a composer install?', $bootstrapPath) . PHP_EOL;
        exit(1);
    }

    require_once $bootstrapPath;
}

$container->build();
$container->mergeParameters($config);
$container->get('console.application')->run();
