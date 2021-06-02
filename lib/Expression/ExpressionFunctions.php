<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;
use RuntimeException;

final class ExpressionFunctions
{
    /**
     * @var array<string,callable>
     */
    private $functionMap = [];

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

    public function execute(string $functionName, array $args): Node
    {
        if (!isset($this->functionMap[$functionName])) {
            throw new RuntimeException(sprintf(
                'Unknown function "%s", known functions "%s"',
                $functionName,
                implode('", "', array_keys($this->functionMap))
            ));
        }

        $function = $this->functionMap[$functionName];
        $evaluated = $function(...$args);

        if (!$evaluated instanceof Node) {
            throw new RuntimeException(sprintf(
                'Function "%s" must return a Node, got "%s"',
                $functionName,
                gettype($evaluated)
            ));
        }

        return $evaluated;
    }
}
