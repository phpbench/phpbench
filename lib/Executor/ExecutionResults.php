<?php

namespace PhpBench\Executor;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use PhpBench\Model\ResultInterface;
use RuntimeException;

/**
 * @implements IteratorAggregate<int, ResultInterface>
 */
final class ExecutionResults implements IteratorAggregate, Countable
{
    /**
     * @var array<int,ResultInterface>
     */
    private $results;

    private function __construct(ResultInterface ...$results)
    {
        $this->results = $results;
    }

    public static function fromResults(ResultInterface ...$results): self
    {
        return new self(...$results);
    }

    public function add(ResultInterface $result): void
    {
        $this->results[] = $result;
    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * @return ArrayIterator<int,ResultInterface>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->results);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->results);
    }

    public function byType(string $target): self
    {
        return new self(...array_filter($this->results, function (ResultInterface $result) use ($target) {
            return $result instanceof $target;
        }));
    }

    public function first(): ResultInterface
    {
        if (empty($this->results)) {
            throw new RuntimeException(
                'Results are empty, cannot get the first one'
            );
        }

        $first = reset($this->results);

        return $first;
    }
}
