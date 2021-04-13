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

namespace PhpBench\Report\Generator;

use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\ReportManager;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Report generator which is a composite of other named reports.
 */
class CompositeGenerator implements GeneratorInterface
{
    /**
     * @var ReportManager
     */
    private $reportManager;

    /**
     */
    public function __construct(ReportManager $reportManager)
    {
        $this->reportManager = $reportManager;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setRequired(['reports']);
        $options->setAllowedTypes('reports', 'array');
    }

    /**
     * {@inheritdoc}
     */
    public function generate(SuiteCollection $collection, Config $config): Reports
    {
        return $this->reportManager->generateReports($collection, $config['reports']);
    }
}
