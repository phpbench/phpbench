<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(__DIR__ . '/../lib/Benchmark/Configuration.php');

use PhpBench\Benchmark\Configuration;

$configPaths = array();

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
        getcwd() . '/.phpbench',
        getcwd() . '/.phpbench.dist',
    );
}

$configuration = null;
foreach ($configPaths as $configPath) {
    if (file_exists($configPath)) {
        $configuration = require_once $configPath;
        break;
    }
}

if (null === $configuration) {
    $bootstrapPath = getcwd() . '/vendor/autoload.php';

    if (!file_exists($bootstrapPath)) {
        echo 'Autoload file "%s" does not exist. Maybe you need to do a composer install?' . PHP_EOL;
        exit(1);
    }

    require_once $bootstrapPath;

    $configuration = new Configuration();
}

if (!$configuration) {
    echo 'The configuration file did not return anything. It must return an instance of PhpBench\\Configuration' . PHP_EOL;
    exit(1);
}

if (!$configuration instanceof PhpBench\Benchmark\Configuration) {
    echo 'The configuration file did not return an instance of PhpBench\\Configuration';
    exit(1);
}

use PhpBench\Console\Application;

$application = new Application($configuration);
$application->run();
