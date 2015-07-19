<?php

namespace PhpBench;

use PhpBench\Console\Application;
use PhpBench\Report\Generator\ConsoleTableGenerator;
use PhpBench\Console\Command\ReportCommand;
use PhpBench\ProgressLogger\DotsProgressLogger;
use PhpBench\Console\Command\RunCommand;
use PhpBench\Extension;
use PhpBench\Result\Dumper\XmlDumper;
use PhpBench\Report\ReportManager;
use PhpBench\ProgressLoggerRegistry;
use PhpBench\Benchmark\Runner;
use PhpBench\Benchmark\CollectionBuilder;
use Symfony\Component\Finder\Finder;
use PhpBench\Benchmark\SubjectBuilder;

class Container
{
    private $instantiators = array();
    private $services = array();
    private $tags = array();
    private $parameters = array();

    public function __construct()
    {
        $this->register('console.application', function (Container $container) {
            $application = new Application();

            foreach (array_keys($container->getServiceIdsForTag('console.command')) as $serviceId) {
                $command = $container->get($serviceId);
                $application->add($command);
            }

            return $application;
        });
        $this->register('benchmark.runner', function (Container $container) {
            return new Runner(
                $container->get('benchmark.collection_builder'),
                $container->get('benchmark.subject_builder'),
                $container->getParameter('config_path')
            );
        });
        $this->register('benchmark.finder', function (Container $container) {
            return new Finder();
        });
        $this->register('benchmark.subject_builder', function (Container $container) {
            return new SubjectBuilder();
        });
        $this->register('benchmark.collection_builder', function (Container $container) {
            return new CollectionBuilder($container->get('benchmark.finder'));
        });
        $this->register('console.command.run_command', function (Container $container) {
            return new RunCommand(
                $container->get('benchmark.runner'),
                $container->get('result.dumper.xml'),
                $container->get('report.manager'),
                $container->get('progress_logger.registry'),
                $container->getParameter('progress_logger_name'),
                $container->getParameter('path'),
                $container->getParameter('enable_gc'),
                $container->getParameter('config_path')
            );
        }, array('console.command' => array()));
        $this->register('result.dumper.xml', function () {
            return new XmlDumper();
        });
        $this->register('report.manager', function () {
            return new ReportManager();
        });
        $this->register('console.command.report_command', function (Container $container) {
            return new ReportCommand();
        }, array('console.command' => array()));
        $this->register('progress_logger.registry', function (Container $container) {
            return new ProgressLoggerRegistry();
        });
        $this->registerProgressLoggers();
        $this->registerReportGenerators();

        $this->parameters = array(
            'enable_gc' => false,
            'path' => __DIR__,
            'extensions' => array(),
            'reports' => array(),
            'config_path' => null,
            'progress_logger_name' => 'benchdots'
        );
    }

    public function build()
    {
        foreach ($this->parameters['extensions'] as $extensionClass) {
            if (!class_exists($extension)) {
                throw new \InvalidArgumentException(sprintf(
                    'Extension class "%s" does not exist',
                    $extensionClass
                ));
            }

            $extension = new $extensionClass;

            if (!$extension instanceof Extension) {
                throw new \InvalidArgumentException(sprintf(
                    'Extensions "%s" must implement the PhpBench\\Extension interface',
                    get_class($extension)
                ));
            }

            $extension->configure($this);
        }

        foreach ($this->getServiceIdsForTag('progress_logger') as $serviceId => $attributes) {
            $progressLogger = $this->get($serviceId);
            $this->get('progress_logger.registry')->addProgressLogger($attributes['name'], $progressLogger);
        }

        foreach ($this->getServiceIdsForTag('report_generator') as $serviceId => $attributes) {
            $reportGenerator = $this->get($serviceId);
            $this->get('report.manager')->addReportGenerator($attributes['name'], $reportGenerator);
        }

        foreach ($this->getParameter('reports') as $reportName => $report) {
            $this->get('report.manager')->addReport($reportName, $report);
        }
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

    public function setExtensions(array $extensions)
    {
        $this->extensions = $extensions;
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

    public function mergeParameters(array $parameters)
    {
        $this->parameters = array_merge(
            $this->parameters,
            $parameters
        );
    }

    public function getParameter($name)
    {
        if (!array_key_exists($name, $this->parameters)) {
            throw new \InvalidArgumentException(sprintf(
                'Parameter "%s" has not been registered',
                $name
            ));
        }

        return $this->parameters[$name];
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
}
