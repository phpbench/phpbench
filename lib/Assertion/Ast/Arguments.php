<?php

namespace PhpBench\Assertion\Ast;

use RuntimeException;

/**
 * @template T
 */
class Arguments
{
    /**
     * @var array<string,T>
     */
    private $arguments;

    /**
     * @param array<string,T> $arguments
     */
    public function __construct(array $arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @return T
     */
    public function get(string $argName)
    {
        if (!array_key_exists($argName, $this->arguments)) {
            throw new RuntimeException(sprintf(
                'Argument "%s" not available, available arguments: "%s"',
                $argName,
                implode('", "', array_keys($this->arguments))
            ));
        }

        return $this->arguments[$argName];
    }

    /**
     * @return array<T>
     */
    public function toArray(): array
    {
        return $this->arguments;
    }
}
