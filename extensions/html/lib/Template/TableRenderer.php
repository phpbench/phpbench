<?php

namespace PhpBench\Extensions\Html\Template;

use function htmlentities;
use PhpBench\Extensions\Html\ObjectRenderer;
use PhpBench\Extensions\Html\ObjectRenderers;
use PhpBench\Report\Model\Table;

class TableRenderer implements ObjectRenderer
{
    public function render(ObjectRenderers $renderer, object $object): ?string
    {
        if (!$object instanceof Table) {
            return null;
        }
        $out = [];

        $out[] = '<table class="table table-striped table-bordered">';
        $out[] = '<thead>';

        if ($object->title()) {
            $out[] = sprintf('<h3>%s</h3>', htmlentities($object->title()));
        }

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
