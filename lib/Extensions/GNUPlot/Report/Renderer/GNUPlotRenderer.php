<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Extensions\GNUPlot\Report\Renderer;


use PhpBench\Dom\Document;
use PhpBench\Console\OutputAwareInterface;
use PhpBench\Report\RendererInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GNUPlotRenderer implements RendererInterface, OutputAwareInterface
{
    private $output;

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function render(Document $report, array $config)
    {
        $dataSets = array();
        foreach ($report->query('.//subject') as $subjectEl) {
            $dataSet = array();
            foreach ($subjectEl->query('.//iteration') as $iterationEl) {
                $dataSet[] = $iterationEl->getAttribute('time');
            }
            $dataSets[] = $dataSet;
        }
        var_dump($dataSets);die();;
    }

    public function getDefaultOutputs()
    {
        return array(
            'rproject' => array(),
        );
    }

    public function getSchema()
    {
        return array();
    }

    public function getDefaultConfig()
    {
        return array();
    }
}
