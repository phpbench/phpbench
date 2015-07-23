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
use PhpBench\Result\Loader\XmlLoader;
use PhpBench\Extension\CoreExtension;

/**
 * PHPBench Container.
 *
 * This is a simple, extendable, closure based dependency injection container.
 */
class Container
{
    private $instantiators = array();
    private $services = array();
    private $tags = array();
    private $parameters = array();

    public function __construct()
    {
        $this->parameters['extensions'] = array(
            'PhpBench\Extension\CoreExtension',
        );
    }

    public function build(array $config = array())
    {
        $extensions = array();
        foreach ($this->parameters['extensions'] as $extensionClass) {
            if (!class_exists($extensionClass)) {
                throw new \InvalidArgumentException(sprintf(
                    'Extension class "%s" does not exist',
                    $extensionClass
                ));
            }

            $extension = new $extensionClass();

            if (!$extension instanceof Extension) {
                throw new \InvalidArgumentException(sprintf(
                    'Extensions "%s" must implement the PhpBench\\Extension interface',
                    get_class($extension)
                ));
            }

            $extension->configure($this, $config);
            $extensions[] = $extension;
        }

        foreach ($extensions as $extension) {
            $extension->build($this, $config);
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
}
