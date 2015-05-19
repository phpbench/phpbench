<?php

namespace PhpBench;

use PhpBench\ReportGenerator;

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
