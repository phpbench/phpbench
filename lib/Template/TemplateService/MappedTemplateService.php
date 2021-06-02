<?php

namespace PhpBench\Template\TemplateService;

use PhpBench\Template\TemplateService;
use RuntimeException;

class MappedTemplateService implements TemplateService
{
    /**
     * @var array
     */
    private $serviceMap;

    public function __construct(array $serviceMap)
    {
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

        return $this->serviceMap[$serviceName];
    }
}
