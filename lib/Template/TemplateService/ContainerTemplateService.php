<?php

namespace PhpBench\Template\TemplateService;

use PhpBench\Template\TemplateService;
use Psr\Container\ContainerInterface;
use RuntimeException;

final class ContainerTemplateService implements TemplateService
{
    /**
     * @param array<string, class-string<object>> $serviceMap
     */
    public function __construct(private readonly ContainerInterface $container, private array $serviceMap)
    {
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
