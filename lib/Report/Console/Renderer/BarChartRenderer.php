<?php

namespace PhpBench\Report\Console\Renderer;

use Generator;
use function mb_substr;
use PhpBench\Expression\ExpressionEvaluator;
use PhpBench\Expression\Printer;
use PhpBench\Report\Console\ObjectRenderer;
use PhpBench\Report\Console\ObjectRendererInterface;
use PhpBench\Report\Model\BarChart;
use PhpBench\Report\Model\BarChartDataSet;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;

class BarChartRenderer implements ObjectRendererInterface
{
    const HEIGHT = 8;
    const COLORS = ['red', 'green', 'blue', 'cyan', 'magenta', 'yellow', 'white'];
    const BLOCKS = ['▁', '▂', '▃', '▄', '▅', '▆', '▇', '█'];

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

        if ($object->title) {
            $output->writeln($object->title);
            $output->write(PHP_EOL);
        }

        if ($object->isEmpty()) {
            return true;
        }

        foreach ($this->renderBarChart($object, $object->xValues()) as $chunk) {
            $output->write($chunk);
        }

        return true;
    }

    /**
     * @param scalar[] $xValues
     * @return Generator<string>
     */
    private function renderBarChart(BarChart $chart, array $xValues): Generator
    {
        $yScale = max($chart->yValues());
        $step = $yScale / self::HEIGHT;
        $height = self::HEIGHT;

        while ($height > 0) {
            yield from $this->printYAxesLabel($step, $height, $chart->yAxesLabel);

            foreach ($xValues as $xIndex => $xValue) {
                foreach ($chart->dataSets as $dataSetIndex => $dataSet) {
                    if (!isset($dataSet->ySeries[$xIndex])) {
                        yield ' ';

                        continue;
                    }

                    $upper = $step * $height;
                    $yValue = $dataSet->ySeries[$xIndex];

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

        // footer
        yield '          └';

        foreach ($xValues as $xIndex => $xValue) {
            foreach ($chart->dataSets as $dataSetIndex => $dataSet) {
                yield '─';
            }
            yield '─';
        }
        yield PHP_EOL;
        yield 'Set #       ';

        foreach ($xValues as $xIndex => $xValue) {
            foreach ($chart->dataSets as $dataSetIndex => $dataSet) {
                if ($dataSetIndex === 0) {
                    yield (string)($xIndex + 1);

                    continue;
                }
                yield ' ';
            }
            yield ' ';
        }

        yield PHP_EOL;
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
        foreach ($object->dataSets as $index => $dataSet) {
            yield sprintf('    %s: <fg=%s>█</> ', $dataSet->name, self::COLORS[$index % count(self::COLORS)]);
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
}
