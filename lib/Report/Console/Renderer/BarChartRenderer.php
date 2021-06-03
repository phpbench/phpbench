<?php

namespace PhpBench\Report\Console\Renderer;

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
    const COLORS = ['red', 'green', 'blue', 'cyan'];

    /**
     * @var string[]
     */
    const BLOCKS = [
        '▁',
        '▂',
        '▃',
        '▄',
        '▅',
        '▆',
        '▇',
        '█'
    ];

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

        $yValues = array_merge(...array_map(function (BarChartDataSet $dataSet) {
            return $dataSet->ySeries;
        }, $object->dataSets));
        $xSeries = array_unique(array_merge(...array_map(function (BarChartDataSet $dataSet) {
            return $dataSet->xSeries;
        }, $object->dataSets)));

        if (empty($yValues)) {
            return true;
        }

        $yScale = max($yValues);
        $step = $yScale / self::HEIGHT;
        $height = self::HEIGHT;

        while ($height > 0) {
            $this->printYLabel($output, $step, $height);

            foreach ($xSeries as $xIndex => $xValue) {
                foreach ($object->dataSets as $dataSetIndex => $dataSet) {
                    if (!isset($dataSet->ySeries[$xIndex])) {
                        $output->write(' ');

                        continue;
                    }

                    $upper = $step * $height;
                    $yValue = $dataSet->ySeries[$xIndex];

                    // render top block (partial)
                    if ($yValue > $upper - $step && $yValue < $upper) {
                        $delta = $upper - $yValue;
                        $percentage = $delta / $step;
                        $block = floor(count(self::BLOCKS) * $percentage);
                        $output->write(
                            sprintf(
                                '<fg=%s>%s</>',
                                self::COLORS[$dataSetIndex % count(self::COLORS)],
                                array_reverse(self::BLOCKS)[$block]
                            )
                        );

                        continue;
                    }

                    // render whole block
                    if ($yValue >= $upper) {
                        $output->write(sprintf('<fg=%s>%s</>', self::COLORS[$dataSetIndex % count(self::COLORS)], self::BLOCKS[7]));

                        continue;
                    }

                    $output->write(' ');
                }
                $output->write(' ');
            }

            $height--;
            $output->write(PHP_EOL);
        }

        // footer
        $output->write('          └');

        foreach ($xSeries as $xIndex => $xValue) {
            foreach ($object->dataSets as $dataSetIndex => $dataSet) {
                $output->write('─');
            }
            $output->write('─');
        }
        $output->write(PHP_EOL);
        $output->write('Set #       ');

        foreach ($xSeries as $xIndex => $xValue) {
            foreach ($object->dataSets as $dataSetIndex => $dataSet) {
                if ($dataSetIndex === 0) {
                    $output->write((string)($xIndex + 1));

                    continue;
                }
                $output->write(' ');
            }
            $output->write(' ');
        }

        $output->write(PHP_EOL);
        $output->write(PHP_EOL);

        $this->writeLegend($object, $output);
        $output->write(PHP_EOL);

        return true;
    }

    private function writeLegend(BarChart $object, OutputInterface $output): void
    {
        foreach ($object->dataSets as $index => $dataSet) {
            $output->write(sprintf('    %s: <fg=%s>█</> ', $dataSet->name, self::COLORS[$index]));
        }
        $output->write(PHP_EOL);
    }

    private function printYLabel(OutputInterface $output, int $step, int $height): void
    {
        $label = Helper::removeDecoration($output->getFormatter(), $this->printer->print($this->evaluator->evaluate('yValue as time precision 1', ['yValue' => $step * $height])));

        $string = '';

        for ($i = 0; $i < 10; $i++) {
            if (mb_strlen($label) < $i) {
                $string .= ' ';

                continue;
            }
            $string .= mb_substr($label, $i, 1);
        }
        $string .= ' │ ';
        $output->write($string);
    }
}
