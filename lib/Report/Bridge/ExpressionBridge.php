<?php

namespace PhpBench\Report\Bridge;

use PhpBench\Data\DataFrame;
use PhpBench\Data\DataFrames;
use PhpBench\Data\Row;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\ExpressionEvaluator;

class ExpressionBridge
{
    /**
     * @var ExpressionEvaluator
     */
    private $evaluator;

    public function __construct(ExpressionEvaluator $evaluator)
    {
        $this->evaluator = $evaluator;
    }

    /**
     * @param parameters $params
     */
    public function evaluate(string $expression, array $params): Node
    {
        return $this->evaluator->evaluate($expression, $params);
    }

    /**
     * @return scalar|scalar[]
     *
     * @param parameters $params
     */
    public function evaluatePhpValue(string $expression, array $params)
    {
        return $this->evaluator->evaluatePhpValue($expression, $params);
    }

    /**
     * Render expressions in a string delimtied by `{{` and `}}`
     *
     * @param parameters $params
     */
    public function renderTemplate(?string $template, array $params): ?string
    {
        return $this->evaluator->renderTemplate($template, $params);
    }

    /**
     * @param string[] $expressions
     */
    public function partition(DataFrame $frame, array $expressions): DataFrames
    {
        return $frame->partition(function (Row $row) use ($expressions) {
            $hash = [];

            foreach ($expressions as $expression) {
                $hash[] = $this->evaluatePhpValue($expression, $row->toRecord());
            }

            return implode('-', $hash);
        });
    }

    /**
     * @return scalar[]
     *
     * @param parameters $params
     */
    public function evaluateColumns(string $colName, array $params): array
    {
        $colNames = [];

        if (substr($colName, 0, 2) !== '@=') {
            return [$colName];
        }

        $expression = substr($colName, 2);
        $colNames = (array)$this->evaluator->evaluatePhpValue($expression, $params);

        return $colNames;
    }
}
