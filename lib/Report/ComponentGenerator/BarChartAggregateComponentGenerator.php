<?php

namespace PhpBench\Report\ComponentGenerator;

use PhpBench\Data\DataFrame;
use PhpBench\Expression\ExpressionEvaluator;
use PhpBench\Report\ComponentGeneratorInterface;
use PhpBench\Report\ComponentInterface;
use PhpBench\Report\Model\BarChart;
use PhpBench\Report\Model\BarChartDataSet;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BarChartAggregateComponentGenerator implements ComponentGeneratorInterface
{
    public const PARAM_X_PARTITION = 'x_partition';
    public const PARAM_SET_PARTITION = 'set_partition';
    public const PARAM_Y_EXPR = 'y_expr';
    public const PARAM_Y_ERROR_MARGIN = 'y_error_margin';
    public const PARAM_TITLE = 'title';
    public const PARAM_DESCRIPTION = 'description';
    public const PARAM_Y_AXES_LABEL = 'y_axes_label';

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
            self::PARAM_TITLE => null,
            self::PARAM_X_PARTITION => [],
            self::PARAM_SET_PARTITION => [],
            self::PARAM_Y_AXES_LABEL => 'yValue',
            self::PARAM_Y_ERROR_MARGIN => null,
        ]);
        $options->setRequired(self::PARAM_Y_EXPR);
        $options->setAllowedTypes(self::PARAM_TITLE, ['string', 'null']);
        $options->setAllowedTypes(self::PARAM_X_PARTITION, ['string[]']);
        $options->setAllowedTypes(self::PARAM_SET_PARTITION, ['string[]']);
        $options->setAllowedTypes(self::PARAM_Y_AXES_LABEL, ['string']);
        $options->setAllowedTypes(self::PARAM_Y_EXPR, ['string']);
        $options->setAllowedTypes(self::PARAM_Y_ERROR_MARGIN, ['string']);
    }

    /**
     * {@inheritDoc}
     */
    public function generateComponent(DataFrame $dataFrame, array $config): ComponentInterface
    {
        $xSeries = [];
        $xLabels = [];
        $errorMargins = [];
        $ySeries = [];

        foreach ($dataFrame->partition($config[self::PARAM_X_PARTITION]) as $xLabel => $partition) {
            $xLabels[] = $xLabel;

            foreach ($partition->partition($config[self::PARAM_SET_PARTITION]) as $setLabel => $setPartition) {
                $yValue = $this->evaluator->evaluatePhpValue($config[self::PARAM_Y_EXPR], [
                    'frame' => $dataFrame,
                    'partition' => $setPartition
                ]);
                assert(is_int($yValue) || is_float($yValue));
                $ySeries[$setLabel][$xLabel] = $yValue;

                if (null === $config[self::PARAM_Y_ERROR_MARGIN]) {
                    continue;
                }
                $errorMargins[$setLabel][$xLabel] = $this->evaluator->evaluatePhpValue($config[self::PARAM_Y_ERROR_MARGIN], [
                    'frame' => $dataFrame,
                    'partition' => $setPartition
                ]);
            }
        }

        $ySeries = $this->normalizeSeries($xLabels, $ySeries);

        if (null !== $config[self::PARAM_Y_ERROR_MARGIN]) {
            $errorMargins = $this->normalizeSeries($xLabels, $errorMargins);
        }

        return new BarChart(
            array_map(function (array $ySeries, ?array $errorMargins, string $setName) use ($xLabels) {
                return new BarChartDataSet(
                    $setName,
                    $xLabels,
                    $ySeries,
                    $errorMargins
                );
            }, (array)$ySeries, (array)$errorMargins, array_keys((array)$ySeries)),
            $this->evaluator->renderTemplate($config[self::PARAM_TITLE], ['frame' => $dataFrame]),
            $config[self::PARAM_Y_AXES_LABEL]
        );
    }

    private function normalizeSeries(array $xLabels, array $ySeries): array
    {
        return array_map(function (array $series) use ($xLabels) {
            return array_values(array_merge(array_combine($xLabels, array_fill(0, count($xLabels), 0)), $series));
        }, $ySeries);
    }
}
