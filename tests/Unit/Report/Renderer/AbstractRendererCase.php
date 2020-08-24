<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Tests\Unit\Report\Renderer;

use PhpBench\Dom\Document;
use PhpBench\Registry\Config;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractRendererCase extends TestCase
{
    abstract protected function getRenderer();

    protected function renderReport($reports, $config)
    {
        $renderer = $this->getRenderer();
        $options = new OptionsResolver();
        $renderer->configure($options);

        $renderer->render($reports, new Config('test', $options->resolve($config)));
    }

    protected function getReportsDocument()
    {
        $document = new Document();
        $report = <<<'EOT'
<?xml version="1.0"?>
<reports name="test_report">
    <report name="test_report" title="Report Title">
        <description>Report Description</description>
        <table>
            <group name="body">
                <row>
                    <cell name="one"><value>Hello</value></cell>
                    <cell name="two"><value>Goodbye</value></cell>
                </row>
            </group>
        </table>
    </report>
</reports>
EOT;
        $document->loadXML($report);

        return $document;
    }
}
