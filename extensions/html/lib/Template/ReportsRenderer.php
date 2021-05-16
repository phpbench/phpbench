<?php

namespace PhpBench\Extensions\Html\Template;

use PhpBench\Extensions\Html\ObjectRenderer;
use PhpBench\Extensions\Html\ObjectRenderers;
use PhpBench\Report\Model\Reports;

class ReportsRenderer implements ObjectRenderer
{
    public function render(ObjectRenderers $renderer, object $object): ?string
    {
        if (!$object instanceof Reports) {
            return null;
        }
        $out = ['<html><body>'];

        foreach ($object as $report) {
            $out[] = $renderer->render($report);
        }
        $out[] = '</body></html>';

        return implode("\n", $out);
    }
}
