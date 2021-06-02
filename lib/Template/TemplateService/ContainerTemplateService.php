<?php

namespace PhpBench\Template\TemplateService;

use PhpBench\Template\TemplateService;
use Psr\Container\ContainerInterface;
use RuntimeException;

final class ContainerTemplateService implements TemplateService
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
                $serviceName,
                implode('", "', array_keys($this->serviceMap))
            ));
        }

        return $this->container->get($this->serviceMap[$serviceName]);
    }
}
