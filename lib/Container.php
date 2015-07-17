<?php

namespace PhpBench;

use PhpBench\Console\Application;
use PhpBench\Report\Generator\ConsoleTableGenerator;
use PhpBench\Console\Command\ReportCommand;
use PhpBench\ProgressLogger\DotsProgressLogger;
use PhpBench\Console\Command\RunCommand;

class Container
{
    private $instantiators = array();
    private $services = array();
    private $tags = array();

    public function build()
    {
        $this->registerConsole();
        $this->registerProgressLoggers();
        $this->registerReportGenerators();

        foreach ($this->getServiceIdsForTag('progress_logger') as $serviceId => $attributes) {
            $progressLogger = $this->get($serviceId);
            $this->get('configuration')->addProgressLogger($attributes['name'], $progressLogger);
        }

        foreach ($this->getServiceIdsForTag('report_generator') as $serviceId => $attributes) {
            $reportGenerator = $this->get($serviceId);
            $this->get('configuration')->addReportGenerator($attributes['name'], $reportGenerator);
        }
    }

    private function registerConsole()
    {
        $this->register('console.application', function (Container $container) {
            $application = new Application($container->get('configuration'));

            foreach ($container->getServiceIdsForTag('console.command') as $serviceId => $attributes) {
                $command = $container->get($serviceId);
                $application->add($command);
            }

            return $application;
        });

        $this->register('console.command.run_command', function (Container $container) {
            return new RunCommand();
        }, array('console.command' => array()));

        $this->register('console.command.report_command', function (Container $container) {
            return new ReportCommand();
        }, array('console.command' => array()));
    }

    private function registerProgressLoggers()
    {
        $this->register('progress_logger.dots', function (Container $container) {
            return new DotsProgressLogger();
        }, array('progress_logger' => array('name' => 'dots')));

        $this->register('progress_logger.benchdots', function (Container $container) {
            return new DotsProgressLogger(true);
        }, array('progress_logger' => array('name' => 'benchdots')));
    }

    private function registerReportGenerators()
    {
        $this->register('report_generator.console_table', function () {
            return new ConsoleTableGenerator();
        }, array('report_generator' => array('name' => 'console_table')));
    }

    public function get($serviceId)
    {
        if (isset($this->services[$serviceId])) {
            return $this->services[$serviceId];
        }

        if (!isset($this->instantiators[$serviceId])) {
            throw new \InvalidArgumentException(sprintf(
                'No instantiator has been registered for requested service "%s"',
                $serviceId
            ));
        }

        $this->services[$serviceId] = $this->instantiators[$serviceId]($this);

        return $this->services[$serviceId];
    }

    public function set($serviceId, $instance)
    {
        $this->services[$serviceId] = $instance;
    }

    public function getServiceIdsForTag($tag)
    {
        $serviceIds = array();
        foreach ($this->tags as $serviceId => $tags) {
            if (isset($tags[$tag])) {
                $serviceIds[$serviceId] = $tags[$tag];
            }
        }

        return $serviceIds;
    }

    public function register($serviceId, \Closure $instantiator, array $tags = array())
    {
        if (isset($this->instantiators[$serviceId])) {
            throw new \InvalidArgumentException(sprintf(
                'Service with ID "%s" has already been registered'
            ));
        }

        $this->instantiators[$serviceId] = $instantiator;

        $this->tags[$serviceId] = $tags;
    }
}
