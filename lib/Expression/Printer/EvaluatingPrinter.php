<?php

namespace PhpBench\Expression\Printer;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class EvaluatingPrinter implements Printer
{
    /**
     * @var array
     */
    private $nodeClasses;

    /**
     * @var Evaluator
     */
    private $evaluator;

    /**
     * @var NodePrinter
     */
    private $printers;

    public function __construct(
        NodePrinter $printers,
        Evaluator $evaluator,
        array $nodeClasses
    ) {
        $this->printers = $printers;
        $this->nodeClasses = $nodeClasses;
        $this->evaluator = $evaluator;
    }

    public function print(Node $node): string
    {
        if (!$this->shouldEvaluate($node)) {
            return $this->printers->print($this, $node);
        }

        $node = $this->evaluator->evaluate($node, $params);

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
