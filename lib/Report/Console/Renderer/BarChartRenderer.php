<?php

namespace PhpBench\Report\Console\Renderer;

use Generator;
use PhpBench\Expression\ExpressionEvaluator;
use PhpBench\Expression\Printer;
use PhpBench\Report\Console\ObjectRenderer;
use PhpBench\Report\Console\ObjectRendererInterface;
use PhpBench\Report\Model\BarChart;
use Symfony\Component\Console\Output\OutputInterface;

use function mb_substr;

class BarChartRenderer implements ObjectRendererInterface
{
    public const HEIGHT = 8;
    public const COLORS = ['red', 'green', 'blue', 'cyan', 'magenta', 'yellow', 'white'];
    public const BLOCKS = ['▁', '▂', '▃', '▄', '▅', '▆', '▇', '█'];

    /**
     * @var Printer
     */
    private $printer;

    /**
     * @var ExpressionEvaluator
     */
    private $evaluator;

    public function __construct(ExpressionEvaluator $evaluator, Printer $printer)
    {
        $this->printer = $printer;
        $this->evaluator = $evaluator;
    }

    public function render(OutputInterface $output, ObjectRenderer $renderer, object $object): bool
    {
        if (!$object instanceof BarChart) {
            return false;
        }

        if ($object->title()) {
            $output->writeln($object->title());
            $output->write(PHP_EOL);
        }

        if ($object->isEmpty()) {
            return true;
        }

        foreach ($this->renderBarChart($object, $object->xAxes()) as $chunk) {
            $output->write($chunk);
        }

        return true;
    }

    /**
     * @param scalar[] $xSeries
     *
     * @return Generator<string>
     */
    private function renderBarChart(BarChart $chart, array $xSeries): Generator
    {
        $yScale = max($chart->yValues());
        $step = $yScale / self::HEIGHT;
        $height = self::HEIGHT;

        while ($height > 0) {
            yield from $this->printYAxesLabel($step, $height, $chart->yAxesLabelExpression());

            foreach ($xSeries as $xIndex => $xValue) {
                foreach ($chart->dataSets() as $dataSetIndex => $dataSet) {
                    $upper = $step * $height;
                    $yValue = $dataSet->yValueAt($xIndex);

                    // render top block (partial)
                    if ($yValue > $upper - $step && $yValue < $upper) {
                        $delta = $upper - $yValue;
                        $percentage = $delta / $step;
                        $block = floor(count(self::BLOCKS) * $percentage);

                        yield sprintf(
                            '<fg=%s>%s</>',
                            self::COLORS[$dataSetIndex % count(self::COLORS)],
                            array_reverse(self::BLOCKS)[$block]
                        );

                        continue;
                    }

                    // render whole block
                    if ($yValue >= $upper) {
                        yield sprintf('<fg=%s>%s</>', self::COLORS[$dataSetIndex % count(self::COLORS)], self::BLOCKS[7]);

                        continue;
                    }

                    yield ' ';
                }

                yield ' ';
            }

            $height--;

            yield PHP_EOL;
        }

        yield from $this->renderXAxes($xSeries, $chart);

        yield PHP_EOL;

        yield from $this->writeLegend($chart);

        yield PHP_EOL;

        return true;
    }

    /**
     * @return Generator<string>
     */
    private function writeLegend(BarChart $object): Generator
    {
        foreach ($object->dataSets() as $index => $dataSet) {
            yield sprintf(
                "[<fg=%s>%s</> %s] ",
                self::COLORS[$index % count(self::COLORS)],
                self::BLOCKS[7],
                $dataSet->name()
            );
        }

        yield PHP_EOL;

        yield PHP_EOL;

        $xLabels = array_map(function (string $label) {
            return mb_strlen($label) > 20 ? mb_substr($label, 0, 19) . '᠁' : $label;
        }, $object->xLabels());

        $padding = max(array_map(function (string $xLabel) {
            return mb_strlen($xLabel);
        }, $xLabels));

        foreach ($xLabels as $index => $xLabel) {
            yield sprintf(
                "<fg=cyan>%s:</> %-".$padding."s ",
                $index + 1,
                $xLabel
            );

            if ($index % 4 === 3) {
                yield PHP_EOL;
            }
        }

        yield PHP_EOL;
    }

    /**
     * @return Generator<string>
     */
    private function printYAxesLabel(float $step, int $height, string $yAxesLabel): Generator
    {
        $label = $this->printer->print($this->evaluator->evaluate(
            $yAxesLabel,
            ['yValue' => $step * $height]
        ));

        $string = '';

        for ($i = 0; $i < 10; $i++) {
            if (mb_strlen($label) < $i) {
                $string .= ' ';

                continue;
            }
            $string .= mb_substr($label, $i, 1);
        }
        $string .= ' │ ';

        yield $string;
    }

    /**
     * @param scalar[] $xSeries
     *
     * @return Generator<string>
     */
    private function renderXAxes(array $xSeries, BarChart $chart): Generator
    {
        yield '          └';

        foreach ($xSeries as $xIndex => $xValue) {
            foreach ($chart->dataSets() as $dataSetIndex => $dataSet) {
                yield '─';
            }

            yield '─';
        }

        yield '─';

        yield PHP_EOL;

        if (count($chart->xAxes()) < 1) {
            return;
        }

        yield '            ';

        foreach ($chart->xAxes() as $xIndex => $xValue) {
            foreach ($chart->dataSets() as $dataSetIndex => $dataSet) {
                if ($dataSetIndex === 0) {
                    yield sprintf('<fg=cyan>%s</>', (string)(($xIndex + 1) % 10));

                    continue;
                }

                yield ' ';
            }

            yield ' ';
        }

        yield PHP_EOL;
    }
}
