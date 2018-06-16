<?php

namespace PhpBench\Registry;

interface ConfigResolverInterface
{
    /**
     * Return the named configuration.
     * 
     */
    public function getConfig($name);

    /**
     * Set a named configuration.
     * 
     * Note that all configurations must be associated with a named service
     * via a configuration key equal to the configuration service type of this registry.
     * 
     */
    public function setConfig($name, array $config);
}
