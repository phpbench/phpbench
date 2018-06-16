<?php

namespace PhpBench\Registry;

interface RegistryInterface
{
    /**
     * Register a service ID with against the given name.
     * 
     */
    public function registerService($name, $serviceId);

    /**
     * Directly set a named service.
     * 
     */
    public function setService($name, $object);

    /**
     * Return the named service, lazily creating it from the container
     * if it has not yet been accessed.
     */
    public function getService($name = null);
}
