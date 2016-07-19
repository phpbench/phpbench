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

namespace PhpBench\Report\Renderer;

use PhpBench\Console\OutputAwareInterface;
use PhpBench\Dom\Document;
use PhpBench\Formatter\Formatter;
use PhpBench\PhpBench;
use PhpBench\Registry\Config;
use PhpBench\Report\RendererInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class XsltRenderer implements RendererInterface, OutputAwareInterface
{
    const DEFAULT_FILENAME = 'report.html';

    /**
     * @var OutputAwareInterface
     */
    private $output;

    /**
     * @var Formatter
     */
    private $formatter;

    public function __construct(Formatter $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Render the table.
     *
     * @param mixed $tableDom
     * @param mixed $config
     */
    public function render(Document $reportDom, Config $config)
    {
        $template = $config['template'];
        $out = strtr(
            $config['file'],
            [
                '%report_name%' => $reportDom->firstChild->getAttribute('name'),
            ]
        );

        if (!file_exists($template)) {
            throw new \RuntimeException(sprintf(
                'XSLT template file "%s" does not exist',
                $template
            ));
        }

        foreach ($reportDom->query('.//row') as $rowEl) {
            $formatterParams = [];
            foreach ($rowEl->query('./formatter-param') as $paramEl) {
                $formatterParams[$paramEl->getAttribute('name')] = $paramEl->nodeValue;
            }

            foreach ($rowEl->query('./cell') as $cellEl) {
                $value = $cellEl->nodeValue;
                if ('' !== $value && $cellEl->hasAttribute('class')) {
                    $classes = explode(' ', $cellEl->getAttribute('class'));
                    $value = $this->formatter->applyClasses($classes, $value, $formatterParams);
                    $cellEl->nodeValue = $value;
                }
            }
        }

        $stylesheetDom = new \DOMDocument('1.0');
        $stylesheetDom->load($template);
        $xsltProcessor = new \XsltProcessor();
        $xsltProcessor->importStylesheet($stylesheetDom);
        $xsltProcessor->setParameter(null, 'title', $config['title']);
        $xsltProcessor->setParameter(null, 'phpbench-version', PhpBench::VERSION);
        $xsltProcessor->setParameter(null, 'date', date('Y-m-d H:i:s'));
        $output = $xsltProcessor->transformToXml($reportDom);

        if (!$output) {
            throw new \InvalidArgumentException(sprintf(
                'Could not render report with XSL file "%s"',
                $template
            ));
        }

        if ($out) {
            file_put_contents($out, $output);
            $this->output->writeln('Dumped XSLT report:');
            $this->output->writeln($out);
        } else {
            $this->output->write($output);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConfig()
    {
        return [
            'title' => 'PHPBench Benchmark Results',
            'template' => __DIR__ . '/templates/html.xsl',
            'file' => null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string',
                ],
                'template' => [
                    'type' => 'string',
                ],
                'file' => [
                    'type' => ['string', 'null'],
                ],
            ],
            'additionalProperties' => false,
        ];
    }
}
