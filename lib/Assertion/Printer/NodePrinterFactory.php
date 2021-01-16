<?php

namespace PhpBench\Assertion\Printer;

use PhpBench\Assertion\ExpressionEvaluator;
use PhpBench\Assertion\ExpressionEvaluatorFactory;
use PhpBench\Assertion\ExpressionPrinter;
use PhpBench\Assertion\ExpressionPrinterFactory;
use PhpBench\Util\TimeUnit;

class NodePrinterFactory implements ExpressionPrinterFactory
{
    /**
     * @var TimeUnit
     */
    private $timeUnit;

    /**
     * @var ExpressionEvaluatorFactory
     */
    private $evaluator;

    public function __construct(TimeUnit $timeUnit, ExpressionEvaluatorFactory $evaluator)
    {
        $this->timeUnit = $timeUnit;
        $this->evaluator = $evaluator;
    }

    public function create(array $args): ExpressionPrinter
    {
        return new NodePrinter($args, $this->timeUnit, $this->evaluator);
    }
}
