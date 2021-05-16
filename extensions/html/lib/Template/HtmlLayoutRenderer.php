<?php

namespace PhpBench\Extensions\Html\Template;

use PhpBench\Extensions\Html\Model\HtmlLayout;
use PhpBench\Extensions\Html\ObjectRenderer;
use PhpBench\Extensions\Html\ObjectRenderers;
use PhpBench\Report\Model\Reports;
use function htmlentities;

class HtmlLayoutRenderer implements ObjectRenderer
{
    public function render(ObjectRenderers $renderer, object $object): ?string
    {
        if (!$object instanceof HtmlLayout) {
            return null;
        }
        $out = ['<html>'];
        $out[] = '<head>';
        foreach ($object->cssPaths() as $cssPath) {
            $out[] = sprintf('<link rel="stylesheet" href="%s">', htmlentities($cssPath));
        }
        $out[] = '</head>';
        $out[] = $renderer->render($object->reports());
        $out[] = '</body></html>';

        return implode("\n", $out);
    }
}
