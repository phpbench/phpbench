<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench;

class Configuration
{
    private $reportGenerators = array();
    private $path;
    private $reports = array();

    public function addReportGenerator($name, ReportGenerator $reportGenerator)
    {
        $this->reportGenerators[$name] = $reportGenerator;
    }

    public function getReportGenerators()
    {
        return $this->reportGenerators;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function addReport($config)
    {
        $this->reports[] = $config;
    }

    public function getReports()
    {
        return $this->reports;
    }

    public function setReports($reports)
    {
        $this->reports = $reports;
    }
}
