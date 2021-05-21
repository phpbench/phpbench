<?php

namespace PhpBench\Template;

use Psr\Container\ContainerInterface;
use RuntimeException;

final class TemplateServiceContainer 
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var array
     */
    private $serviceMap;

    public function __construct(ContainerInterface $container, array $serviceMap)
    {
        $this->container = $container;
        $this->serviceMap = $serviceMap;
    }

    public function get(string $serviceName): object
    {
        if (!isset($this->serviceMap[$serviceName])) {
            throw new RuntimeException(sprintf(
                'Unknown template service "%s", known template services: "%s"',
                $serviceFqn, implode('", "', array_keys($this->serviceMap))
            ));
        }

        return $this->container->get($this->serviceMap[$serviceName]);
    }
}
