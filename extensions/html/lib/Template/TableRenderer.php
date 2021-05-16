<?php

namespace PhpBench\Extensions\Html\Template;

use PhpBench\Extensions\Html\ObjectRenderer;
use PhpBench\Extensions\Html\ObjectRenderers;
use PhpBench\Report\Model\Table;
use function htmlentities;

class TableRenderer implements ObjectRenderer
{
    public function render(ObjectRenderers $renderer, object $object): ?string
    {
        if (!$object instanceof Table) {
            return null;
        }
        $out = [];
        if ($object->title()) {
            $out[] = sprintf('<h2>%s</h2>', htmlentities($object->title()));
        }

        $out[] = '<table class="table"><thead>';
        foreach ($object->columnNames() as $name) {
            $out[] = sprintf('<th>%s</th>', $name);
        }
        $out[] = '</thead><tbody>';

        foreach ($object->rows() as $row) {
            $out[] = '<tr>';
            foreach ($row->cells() as $cell) {
                $out[] = sprintf('<td>%s</td>', $renderer->render($cell));
            }
            $out[] = '</tr>';
        }
        $out[] = '</tbody></table>';

        return implode("\n", $out);
    }
}
