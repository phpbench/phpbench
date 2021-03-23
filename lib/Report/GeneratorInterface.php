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

namespace PhpBench\Report;

use PhpBench\Dom\Document;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Registry\RegistrableInterface;
use PhpBench\Report\Model\Report;
use PhpBench\Report\Model\Reports;

interface GeneratorInterface extends RegistrableInterface
{
    /**
     * Generate the report document from the suite result document.
     *
     */
    public function generate(SuiteCollection $collection, Config $config): Reports;
}
