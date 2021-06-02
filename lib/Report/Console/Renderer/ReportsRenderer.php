<?php

namespace PhpBench\Report\Console\Renderer;

use PhpBench\Report\Console\ObjectRenderer;
use PhpBench\Report\Console\ObjectRendererInterface;
use PhpBench\Report\Model\Reports;
use Symfony\Component\Console\Output\OutputInterface;

class ReportsRenderer implements ObjectRendererInterface
{
    public function render(OutputInterface $output, ObjectRenderer $renderer, object $object): bool
    {
        if (!$object instanceof Reports) {
            return false;
        }

        foreach ($object as $report) {
            $renderer->render($report);
        }

        return true;
    }
}
