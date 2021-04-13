<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\Exception\PrinterError;
use PhpBench\Expression\Printer;
use PhpBench\Expression\Printer\UnderlinePrinterFactory;

class PrettyErrorEvaluator implements Evaluator
{
    /**
     * @var Evaluator
     */
    private $innerEvaluator;

    /**
     * @var Printer
     */
    private $printer;

    /**
     * @var Printer\UnderlinePrinterFactory
     */
    private $underlineFactory;

    public function __construct(
        Evaluator $innerEvaluator,
        Printer $printer,
        UnderlinePrinterFactory $underlineFactory
    ) {
        $this->innerEvaluator = $innerEvaluator;
        $this->printer = $printer;
        $this->underlineFactory = $underlineFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function evaluateType(Node $node, string $expectedType, array $params): Node
    {
        try {
            return $this->innerEvaluator->evaluateType($node, $expectedType, $params);
        } catch (EvaluationError $error) {
            throw $this->prettyError($node, $error, $params);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function evaluate(Node $node, array $params): Node
    {
        try {
            return $this->innerEvaluator->evaluate($node, $params);
        } catch (EvaluationError $error) {
            throw $this->prettyError($node, $error, $params);
        }
    }

    /**
     * @param parameters $params
     */
    private function prettyError(Node $rootNode, EvaluationError $error, array $params): EvaluationError
    {
        try {
            return new EvaluationError($error->node(), implode(PHP_EOL, [
                sprintf('%s:', $error->getMessage()),
                '',
                '    ' . $this->printer->print($rootNode),
                '    ' . $this->underlineFactory->underline($error->node())->print($rootNode),
            ]), $error);
        } catch (PrinterError $printError) {
            throw $error;
        }
    }
}
