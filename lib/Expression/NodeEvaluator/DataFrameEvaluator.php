<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Data\DataFrame;
use PhpBench\Data\Exception\ColumnDoesNotExist;
use PhpBench\Data\Row;
use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\DataFrameNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\Ast\PhpValueFactory;
use PhpBench\Expression\Ast\ScalarValue;
use PhpBench\Expression\Evaluator;

final class DataFrameEvaluator
{
    /**
     * @param parameters $params
     *
     * @return mixed
     */
    public function evaluate(Evaluator $evaluator, DataFrameNode $frameNode, Node $accessNode, array $params, bool $nullSafe)
    {
        if ($accessNode instanceof ScalarValue) {
            try {
                return PhpValueFactory::fromValue($frameNode->dataFrame()->column($accessNode->value())->toValues());
            } catch (ColumnDoesNotExist $notExist) {
                if ($nullSafe) {
                    return new NullNode();
                }

                throw $notExist;
            }
        }

        return new DataFrameNode($this->filterDataFrame($evaluator, $frameNode->dataFrame(), $accessNode));
    }

    private function filterDataFrame(Evaluator $evaluator, DataFrame $dataFrame, Node $accessNode): DataFrame
    {
        return $dataFrame->filter(function (Row $row) use ($evaluator, $accessNode) {
            $result = $evaluator->evaluate($accessNode, $row->toRecord());

            if (!$result instanceof BooleanNode) {
                return false;
            }

            return $result->value();
        });
    }
}
