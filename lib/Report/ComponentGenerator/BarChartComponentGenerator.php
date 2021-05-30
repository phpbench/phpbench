<?php

namespace PhpBench\Report\ComponentGenerator;

use PhpBench\Data\DataFrame;
use PhpBench\Expression\ExpressionEvaluator;
use PhpBench\Report\ComponentGeneratorInterface;
use PhpBench\Report\ComponentInterface;
use PhpBench\Report\Model\BarChart;
use PhpBench\Report\Model\BarChartDataSet;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BarChartComponentGenerator implements ComponentGeneratorInterface
{
    public const PARAM_X_PARTITION = 'x_partition';
    public const PARAM_SET_PARTITION = 'set_partition';
    public const PARAM_Y_EXPR = 'y_expr';
    public const PARAM_Y_ERROR_MARGIN = 'y_error_margin';

    /**
     * @var ExpressionEvaluator
     */
    private $evaluator;

    public function __construct(ExpressionEvaluator $evaluator)
    {
        $this->evaluator = $evaluator;
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setDefaults([
            self::PARAM_X_PARTITION => [],
            self::PARAM_SET_PARTITION => [],
        ]);
        $options->setRequired(self::PARAM_Y_EXPR);
        $options->setRequired(self::PARAM_Y_ERROR_MARGIN);
    }

    /**
     * {@inheritDoc}
     */
    public function generateComponent(DataFrame $dataFrame, array $config): ComponentInterface
    {
        $xSeries = [];
        $xLabels = [];
        $errorMargins = [];
        $sets = $errorMargins = [];

        foreach ($dataFrame->partition($config[self::PARAM_X_PARTITION]) as $xLabel => $partition) {
            $xLabels[] = $xLabel;

            foreach ($partition->partition($config[self::PARAM_SET_PARTITION]) as $setLabel => $setPartition) {
                $sets[$setLabel][] = $this->evaluator->evaluatePhpValue($config[self::PARAM_Y_EXPR], [
                    'frame' => $dataFrame,
                    'partition' => $setPartition
                ]);
                $errorMargins[$setLabel][] = $this->evaluator->evaluatePhpValue($config[self::PARAM_Y_ERROR_MARGIN], [
                    'frame' => $dataFrame,
                    'partition' => $setPartition
                ]);
            }
        }

        return new BarChart(array_map(function (array $ySeries, array $errorMargins, string $setName) use ($xLabels) {
            return new BarChartDataSet($setName, $xLabels, $ySeries, $errorMargins);
        }, (array)$sets, (array)$errorMargins, array_keys((array)$sets)));
    }
}
