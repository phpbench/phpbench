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

if ((!includeIfExists(__DIR__.'/../vendor/autoload.php')) && (!includeIfExists(__DIR__.'/../../../autoload.php'))) {
    fwrite(STDERR,
        'You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'curl -s http://getcomposer.org/installer | php'.PHP_EOL.
        'php composer.phar install'.PHP_EOL
    );
    exit(1);
}

use Symfony\Component\Console\Application;
use PhpBench\Console\Command\BenchRunCommand;

$application = new Application();
$application->add(new BenchRunCommand());
$application->run();
