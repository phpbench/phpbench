<?php

namespace PhpBench\Registry;

use RuntimeException;

final class Registries
{
    /**
     * @param array<string,Registry<object>> $registries
     */
    public function __construct(private array $registries)
    {
    }
    /**
     * @return Registry<object>
     */
    public function get(string $name): Registry
    {
        if (!isset($this->registries[$name])) {
            throw new RuntimeException(sprintf(
                'Registry "%s" not found, known registries: "%s"',
                $name,
                implode('", "', array_keys($this->registries))
            ));
        }

        return $this->registries[$name];
    }
}
