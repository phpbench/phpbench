<?php

namespace PhpBench\Expression\Printer;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class EvaluatingPrinter implements Printer
{
    /**
     * @var parameters
     */
    private array $params;

    /**
     * @param array<class-string<Node>> $nodeClasses
     * @param parameters $params
     */
    public function __construct(
        private readonly NodePrinter $printers,
        private readonly Evaluator $evaluator,
        private readonly array $nodeClasses,
        array $params = []
    ) {
        $this->params = $params;
    }

    /**
     * @param parameters $params
     */
    public function withParams(array $params): self
    {
        return new self($this->printers, $this->evaluator, $this->nodeClasses, $params);
    }

    public function print(Node $node): string
    {
        if (!$this->shouldEvaluate($node)) {
            return $this->printers->print($this, $node);
        }

        $node = $this->evaluator->evaluate($node, $this->params);

        return $this->printers->print($this, $node);
    }

    private function shouldEvaluate(Node $node): bool
    {
        foreach ($this->nodeClasses as $nodeClass) {
            if ($node instanceof $nodeClass) {
                return true;
            }
        }

        return false;
    }
}
