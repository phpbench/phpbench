<?php

namespace PhpBench\Expression;

use Error;
use PhpBench\Assertion\Exception\ExpressionEvaluatorError;
use PhpBench\Expression\Exception\EvaluationError;
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

    /**
     * @param mixed[] $args
     *
     * @return mixed
     */
    public function execute(string $functionName, array $args)
    {
        if (!isset($this->functionMap[$functionName])) {
            throw new RuntimeException(sprintf(
                'Unknown function "%s"',
                $functionName
            ));
        }

        $function = $this->functionMap[$functionName];

        try {
            return $function(...$args);
        } catch (Error $err) {
            throw new RuntimeException($err->getMessage());
        }
    }
}
