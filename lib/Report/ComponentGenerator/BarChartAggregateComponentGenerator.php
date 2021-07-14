<?php

namespace PhpBench\Report\ComponentGenerator;

use PhpBench\Compat\SymfonyOptionsResolverCompat;
use PhpBench\Data\DataFrame;
use PhpBench\Expression\ExpressionEvaluator;
use PhpBench\Report\ComponentGeneratorInterface;
use PhpBench\Report\ComponentInterface;
use PhpBench\Report\Model\BarChart;
use PhpBench\Report\Model\BarChartDataSet;
use RuntimeException;
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
    public const PARAM_X_AXES_LABEL = 'x_axes_label';

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
            self::PARAM_X_AXES_LABEL => null,
            self::PARAM_Y_ERROR_MARGIN => null,
        ]);
        $options->setRequired(self::PARAM_Y_EXPR);
        $options->setAllowedTypes(self::PARAM_TITLE, ['string', 'null']);
        $options->setAllowedTypes(self::PARAM_X_PARTITION, ['string[]']);
        $options->setAllowedTypes(self::PARAM_SET_PARTITION, ['string[]']);
        $options->setAllowedTypes(self::PARAM_Y_AXES_LABEL, ['string']);
        $options->setAllowedTypes(self::PARAM_X_AXES_LABEL, ['null', 'string']);
        $options->setAllowedTypes(self::PARAM_Y_EXPR, ['string']);
        $options->setAllowedTypes(self::PARAM_Y_ERROR_MARGIN, ['string', 'null']);
        SymfonyOptionsResolverCompat::setInfos($options, [
            self::PARAM_TITLE => 'Title for the barchart',
            self::PARAM_X_PARTITION => 'Group by these columns on the X-Axis. The label will be the concatenation of the values of these columns by default',
            self::PARAM_SET_PARTITION => 'Create separate bars for each step by partitioning the data based on these values.',
            self::PARAM_Y_AXES_LABEL => 'Expression to evaluate to determine the Y-Axis label, is passed ``yValue`` (actual value of Y), ``partition`` (the set partition) and ``frame`` (the entire data frame) ',
            self::PARAM_X_AXES_LABEL => 'Expression to evaluate to determine the X-Axis label, is passed ``xValue`` (default X value according to the X-partition), ``partition`` (the x-partition), and ``frame`` (the entire data frame)',
            self::PARAM_Y_EXPR => 'Expression to evaluate the Y-Axis value, e.g. ``mode(partition["result_time_avg"])``',
            self::PARAM_Y_ERROR_MARGIN => 'Expression to evaluate to determine the error margin to show on the chart. Leave as NULL to disable the error margin',
        ]);
    }

    /*
     * {@inheritDoc}
     */
    public function generateComponent(DataFrame $dataFrame, array $config): ComponentInterface
    {
        $xSeries = [];
        $xAxes = [];
        $xLabels = [];
        $errorMargins = [];
        $ySeries = [];

        foreach ($dataFrame->partition($config[self::PARAM_X_PARTITION]) as $xLabel => $partition) {
            $xAxes[] = $xLabel;
            $xLabels[] = $this->resolveXLabel($partition, (string)$xLabel, $config);

            foreach ($partition->partition($config[self::PARAM_SET_PARTITION]) as $setLabel => $setPartition) {
                $yValue = $this->evaluator->evaluatePhpValue($config[self::PARAM_Y_EXPR], [
                    'frame' => $dataFrame,
                    'partition' => $setPartition
                ]);

                if (!is_int($yValue) && !is_float($yValue)) {
                    throw new RuntimeException(sprintf(
                        'Y-Expression must evaluate to an int or a float, got "%s"',
                        gettype($yValue)
                    ));
                }

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

        $ySeries = $this->normalizeSeries($xAxes, $ySeries);

        if (null !== $config[self::PARAM_Y_ERROR_MARGIN]) {
            $errorMargins = $this->normalizeSeries($xAxes, $errorMargins);
        }

        return new BarChart(
            array_map(function (array $ySeries, ?array $errorMargins, string $setName) use ($xAxes) {
                return new BarChartDataSet(
                    $setName,
                    $xAxes,
                    $ySeries,
                    $errorMargins
                );
            }, (array)$ySeries, (array)$errorMargins, array_keys((array)$ySeries)),
            $this->evaluator->renderTemplate($config[self::PARAM_TITLE], ['frame' => $dataFrame]),
            $config[self::PARAM_Y_AXES_LABEL],
            $xLabels
        );
    }

    private function normalizeSeries(array $xLabels, array $ySeries): array
    {
        return array_map(function (array $series) use ($xLabels) {
            return array_values(array_replace(
                array_combine($xLabels, array_fill(0, count($xLabels), 0)),
                $series
            ));
        }, $ySeries);
    }

    /**
     *
     * @param parameters $config
     */
    private function resolveXLabel(DataFrame $partition, string $xLabel, array $config): string
    {
        if (!$config[self::PARAM_X_AXES_LABEL]) {
            return $xLabel;
        }

        $xLabel = $this->evaluator->evaluatePhpValue(
            $config[self::PARAM_X_AXES_LABEL],
            [
                'partition' => $partition,
                'xValue' => $xLabel
            ]
        );

        if (!is_scalar($xLabel)) {
            throw new RuntimeException(sprintf(
                '`%s` value must evaluate to a scalar got "%s" (try using `first(partition["my_column"])`)',
                self::PARAM_X_AXES_LABEL,
                gettype($xLabel)
            ));
        }

        return (string)$xLabel;
    }
}
