<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use PhpBench\Result\SuiteResult;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use PhpBench\OptionsResolver\OptionsResolver;
use PhpBench\Console\OutputAware;

abstract class BaseCommand extends Command
{
}
