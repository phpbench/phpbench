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

use Symfony\Component\Console\Output\NullOutput;
use PhpBench\ReportGenerator\XmlTableReportGenerator;

class XmlReportGeneratorTest extends BaseTabularReportGeneratorCase
{
    public function getReport()
    {
        $output = new NullOutput();

        return new XmlTableReportGenerator($output);
    }
}
