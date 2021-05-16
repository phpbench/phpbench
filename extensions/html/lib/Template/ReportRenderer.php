<?php

namespace PhpBench\Extensions\Html\Template;

use PhpBench\Extensions\Html\ObjectRenderer;
use PhpBench\Extensions\Html\ObjectRenderers;
use PhpBench\Report\Model\Report;
use function htmlentities;

class ReportRenderer implements ObjectRenderer
{
    public function render(ObjectRenderers $renderer, object $object): ?string
    {
        if (!$object instanceof Report) {
            return null;
        }

        if ($object->title()) {
            $out[] = sprintf('<h2>%s</h2>', htmlentities($object->title()));
        }
        if ($object->description()) {
            $out[] = sprintf('<p>%s</p>', htmlentities($object->description()));
        }

        foreach ($object->tables() as $table) {
            $out[] = $renderer->render($table);
        }

        return implode("\n", $out);
    }
}
