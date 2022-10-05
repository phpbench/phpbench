<?php

namespace PhpBench\Model\Result;

use PhpBench\Model\ResultInterface;

class BufferResult implements ResultInterface
{
    /**
     * @var string
     */
    private $buffer;

    public function __construct(string $buffer)
    {
        $this->buffer = $buffer;
    }

    /**
     * {@inheritDoc}
     */
    public static function fromArray(array $values): ResultInterface
    {
        return new self($values['buffer']);
    }

    /**
     * {@inheritDoc}
     */
    public function getMetrics()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getKey(): string
    {
        return 'buffer';
    }
}
