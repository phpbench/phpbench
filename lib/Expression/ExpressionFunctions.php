<?php

namespace PhpBench\Expression;

use RuntimeException;

final class ExpressionFunctions
{
    /**
     * @var array<string,callable>
     */
    private array $functionMap = [];

    /**
     * @param array<string, callable> $functionMap
     */
    public function __construct(array $functionMap)
    {
        foreach ($functionMap as $name => $callable) {
            $this->add($name, $callable);
        }
    }

    /**
     * @return string[]
     */
    public function names(): array
    {
        return array_keys($this->functionMap);
    }

    private function add(string $name, callable $callable): void
    {
        $this->functionMap[$name] = $callable;
    }

    public function get(string $name): callable
    {
        if (!isset($this->functionMap[$name])) {
            throw new RuntimeException(sprintf(
                'Unknown function "%s", known functions "%s"',
                $name,
                implode('", "', array_keys($this->functionMap))
            ));
        }

        return $this->functionMap[$name];
    }
}
