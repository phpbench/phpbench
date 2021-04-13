<?php

namespace PhpBench\Executor;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use PhpBench\Model\ResultInterface;

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
}
