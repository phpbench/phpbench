<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Report\Renderer;

use PhpBench\Dom\Document;

abstract class AbstractRendererCase extends \PHPUnit_Framework_TestCase
{
    public function getReportsDocument()
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
                    <cell name="one">Hello</cell>
                    <cell name="two">Goodbye</cell>
                </row>
            </group>
        </table>
    </report>
</reports>
EOT;
        $document->loadXml($report);

        return $document;
    }
}
