<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\DependencyInjection;

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
    private $extensions = array();

    public function __construct(array $extensions = array())
    {
        $this->parameters['extensions'] = $extensions;

        // Add the core extension by deefault
        $this->parameters['extensions'][] = 'PhpBench\Extension\CoreExtension';
    }

    /**
     * Configure the container. This method will call the `configure()` method
     * on each extension. Extensions must use this opportunity to register their
     * services and define any default parameters.
     *
     * This method must be called before `build()`.
     */
    public function configure()
    {
        foreach ($this->parameters['extensions'] as $extensionClass) {
            if (!class_exists($extensionClass)) {
                throw new \InvalidArgumentException(sprintf(
                    'Extension class "%s" does not exist',
                    $extensionClass
                ));
            }

            $extension = new $extensionClass();

            if (!$extension instanceof ExtensionInterface) {
                throw new \InvalidArgumentException(sprintf(
                    // add any manually specified extensions
                    'Extensions "%s" must implement the PhpBench\\Extension interface',
                    get_class($extension)
                ));
            }

            $extension->configure($this);
            $this->extensions[] = $extension;
        }
    }

    /**
     * Build the container. This method will call the `build()` method on each registered extension.
     * The extensions can use this as a "compiler pass" to add tagged services to other services for example.
     */
    public function build()
    {
        foreach ($this->extensions as $extension) {
            $extension->build($this);
        }
    }

    /**
     * Instantiate and return the service with the given ID.
     * Note that this method will return the same instance on subsequent calls.
     *
     * @param string $serviceId
     *
     * @return mixed
     */
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

    /**
     * Set a service instance.
     *
     * @param string $serviceId
     * @param mixed $instance
     */
    public function set($serviceId, $instance)
    {
        $this->services[$serviceId] = $instance;
    }

    /**
     * Return services IDs for the given tag.
     *
     * @param string $tag
     *
     * @return string[]
     */
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

    /**
     * Register a service with the given ID and instantiator.
     *
     * The instantiator is a closure which accepts an instance of this container and
     * returns a new instance of the service class.
     *
     * @param string $serviceId
     * @param \Closure $instantiator
     * @param string[] $tags
     */
    public function register($serviceId, \Closure $instantiator, array $tags = array())
    {
        if (isset($this->instantiators[$serviceId])) {
            throw new \InvalidArgumentException(sprintf(
                'Service with ID "%s" has already been registered', $serviceId));
        }

        $this->instantiators[$serviceId] = $instantiator;
        $this->tags[$serviceId] = $tags;
    }

    /**
     * Merge an array of parameters onto the existing parameter set.
     *
     * @param array $parameters
     */
    public function mergeParameters(array $parameters)
    {
        $this->parameters = array_merge(
            $this->parameters,
            $parameters
        );
    }

    /**
     * Set the value of the parameter with the given name.
     *
     * @param string $name
     * @param mixed $value
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * Return the parameter with the given name.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
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

    /**
     * Return true if the named parameter exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasParameter($name)
    {
        return array_key_exists($name, $this->parameters);
    }
}
