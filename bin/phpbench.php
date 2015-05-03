<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use PhpBench\Console\Command\BenchRunCommand;

$application = new Application();
$application->add(new BenchRunCommand());
$application->run();
