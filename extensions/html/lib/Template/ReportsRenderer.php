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
        foreach ($object->tables() as $table) {
            $out[] = $renderer->render($table);
        }
        $out[] = '</body></html>';

        return implode("\n", $out);
    }
}
