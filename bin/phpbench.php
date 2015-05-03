<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PhpBench\Console\Command\BenchRunCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new BenchRunCommand());
$application->run();
