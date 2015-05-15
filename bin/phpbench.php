<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function includeIfExists($file)
{
    if (file_exists($file)) {
        return include $file;
    }
}

$autoloadPath = getcwd() . '/vendor/autoload.php';
$configPaths = array(
    getcwd() . '/.phpbench',
    getcwd() . '/.phpbench.dist',
);

if (!file_exists($autoloadPath)) {
    fwrite(STDERR,
        'You must execute phpbench from the project root and set up the project' .
        'dependencies.'
    );
    exit(1);
}

foreach ($configPaths as $configPath) {
    if (file_exists($configPath)) {
        require_once $configPath;
        break;
    }
}

require_once $autoloadPath;

use PhpBench\Console\Application;

$application = new Application();
$application->run();
