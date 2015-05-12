<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Functional\ReportGenerator;

use PhpBench\ReportGenerator\ConsoleTableReportGenerator;
use Symfony\Component\Console\Output\NullOutput;

class ConsoleReportGeneratorTest extends BaseTabularReportGeneratorCase
{
    public function getReport()
    {
        $output = new NullOutput();

        return new ConsoleTableReportGenerator($output);
    }
}
