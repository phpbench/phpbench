<?php

namespace PhpBench\Expression\Exception;

use PhpBench\Expression\Ast\Node;
use RuntimeException;
use Throwable;

class EvaluationError extends RuntimeException
{
    /**
     * @var Node
     */
    private $node;

    public function __construct(Node $node, string $message, ?Throwable $previous = null)
    {
        $this->node = $node;
        parent::__construct($message, 0, $previous);
    }

    public function node(): Node
    {
        return $this->node;
    }
}
