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

namespace PhpBench\Console\Command\Handler;

use PhpBench\Util\TimeUnit;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class TimeUnitHandler
{
    private $timeUnit;

    public function __construct(
        TimeUnit $timeUnit
    ) {
        $this->timeUnit = $timeUnit;
    }

    public static function configure(Command $command)
    {
        $command->addOption('time-unit', null, InputOption::VALUE_REQUIRED, 'Override the time unit');
        $command->addOption('precision', null, InputOption::VALUE_REQUIRED, 'Override the measurement precision');
        $command->addOption('mode', null, InputOption::VALUE_REQUIRED, 'Override the unit display mode ("throughput", "time")');
    }

    public function timeUnitFromInput(InputInterface $input)
    {
        $timeUnit = $input->getOption('time-unit');
        $mode = $input->getOption('mode');
        $precision = $input->getOption('precision');

        if ($timeUnit) {
            $this->timeUnit->overrideDestUnit($timeUnit);
        }

        if ($mode) {
            $this->timeUnit->overrideMode($mode);
        }

        if (null !== $precision) {
            $this->timeUnit->overridePrecision($precision);
        }
    }
}
