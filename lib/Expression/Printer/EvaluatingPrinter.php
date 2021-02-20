<?php

namespace PhpBench\Expression\Printer;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\PrinterError;
use PhpBench\Expression\NodePrinters;
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
     * @var NodePrinters
     */
    private $printers;

    public function __construct(
        NodePrinters $printers,
        Evaluator $evaluator,
        array $nodeClasses
    )
    {
        $this->printers = $printers;
        $this->nodeClasses = $nodeClasses;
        $this->evaluator = $evaluator;
    }

    public function print(Node $node, array $params): string
    {
        $original = $this->printers->print($this, $node, []);

        if (!$this->shouldEvaluate($node)) {
            return $original;
        }

        $node = $this->evaluator->evaluate($node);
        $evaluated = $this->printers->print($this, $node, []);
        $evaluated = $this->pad(mb_strlen($original), mb_strlen($evaluated), $evaluated);

        return $evaluated;

    }

    private function shouldEvaluate(Node $node)
    {
        foreach ($this->nodeClasses as $nodeClass) {
            if ($node instanceof $nodeClass) {
                return true;
            }
        }

        return false;
    }

    private function pad(int $origLen, int $evalLen, string $text): string
    {
        $string = str_repeat(' ', $origLen);
        if ($origLen > $evalLen) {
            $lpad = floor($origLen / 2) - floor($evalLen / 2);
            $r = substr_replace($string, $text, $lpad, $evalLen);
            return $r;
        }

        return $text;
    }
}
