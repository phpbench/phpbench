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

use PhpBench\Report\Generator\ConsoleTableReportGenerator;

class ConsoleReportGeneratorTest extends BaseTabularReportGeneratorCase
{
    public function getReport()
    {
        return new ConsoleTableReportGenerator();
    }
}
