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

use PhpBench\Exception\InvalidArgumentException;

class Configuration
{
    private $reportGenerators = array();
    private $progressLoggers = array();
    private $path;
    private $reports = array();
    private $configPath = null;
    private $progress;
    private $enableGc = false;

    public function addReportGenerator($name, ReportGenerator $reportGenerator)
    {
        $this->reportGenerators[$name] = $reportGenerator;
    }

    public function getReportGenerators()
    {
        return $this->reportGenerators;
    }

    public function addProgressLogger($name, ProgressLogger $progressLogger)
    {
        $this->progressLoggers[$name] = $progressLogger;
    }

    public function getProgressLogger($name)
    {
        if (!isset($this->progressLoggers[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Unknown progress logger "%s", known progress loggers: "%s"',
                $name, implode('", "', array_keys($this->progressLoggers))
            ));
        }

        return $this->progressLoggers[$name];
    }

    public function setProgress($name)
    {
        $this->progress = $name;
    }

    public function getProgress()
    {
        return $this->progress;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function addReport(array $config)
    {
        $this->reports[] = $config;
    }

    public function getReports()
    {
        return $this->reports;
    }

    public function setReports(array $reports)
    {
        $this->reports = $reports;
    }

    public function setConfigPath($path)
    {
        $this->configPath = $path;
    }

    public function getConfigPath()
    {
        return $this->configPath;
    }

    public function enableGc()
    {
        $this->enableGc = true;
    }

    public function getGcEnabled()
    {
        return $this->enableGc;
    }
}
