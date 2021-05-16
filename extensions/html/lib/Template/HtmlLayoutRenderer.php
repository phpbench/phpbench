<?php

namespace PhpBench\Extensions\Html\Template;

use PhpBench\Extensions\Html\Model\HtmlLayout;
use PhpBench\Extensions\Html\ObjectRenderer;
use PhpBench\Extensions\Html\ObjectRenderers;
use PhpBench\PhpBench;
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
        $out[] = '<body>';
        $out[] = '<div class="container-fluid">';
        $out[] = $renderer->render($object->reports());
        $out[] = '</div>';
        $out[] = '<footer>';
        $out[] = '<hr/>';
        $out[] = '<center>';
        $out[] = sprintf('Generated with <a href="https://github.com/phpbench/phpbench">PHPBench</a> %s', PhpBench::version());
        $out[] = '</center>';
        $out[] = '</footer>';
        $out[] = '</body></html>';

        return implode("\n", $out);
    }
}
