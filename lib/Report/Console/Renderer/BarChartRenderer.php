<?php

namespace PhpBench\Report\Console\Renderer;

use PhpBench\Report\Console\ObjectRenderer;
use PhpBench\Report\Console\ObjectRendererInterface;
use PhpBench\Report\Model\BarChart;
use Symfony\Component\Console\Output\OutputInterface;

class BarChartRenderer implements ObjectRendererInterface
{
    public function render(OutputInterface $output, ObjectRenderer $renderer, object $object): bool
    {
        if (!$object instanceof BarChart) {
            return false;
        }

        $output->writeln(sprintf('<comment>// Bar chart rendering in console not supported (%s)</comment>', $object->title));
        $output->write(PHP_EOL);

        return true;
    }
}
