<?php

namespace PhpBench\Expression\Exception;

use PhpBench\Expression\Ast\Node;
use RuntimeException;

class EvaluationError extends RuntimeException
{
    /**
     * @var Node
     */
    private $node;

    public function __construct(Node $node, string $message)
    {
        $this->node = $node;
        parent::__construct($message);
    }

    public function node(): Node
    {
        return $this->node;
    }
}
