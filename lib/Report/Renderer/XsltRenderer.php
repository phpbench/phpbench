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
use Symfony\Component\OptionsResolver\OptionsResolver;

class XsltRenderer implements RendererInterface, OutputAwareInterface
{
    const DEFAULT_FILENAME = 'report.html';

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Formatter
     */
    private $formatter;

    public function __construct(Formatter $formatter)
    {
        if (!extension_loaded($ext = 'xsl')) {
            throw new \RuntimeException(sprintf(
                'The XsltRenderer requires the `%s` extension to be loaded',
                $ext
            ));
        }

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
     * @param Document $reportDom
     * @param Config $config
     */
    public function render(Document $reportDom, Config $config)
    {
        $template = $config['template'];

        $out = strtr(
            $config['file'],
            [
                // @phpstan-ignore-next-line
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
                foreach ($cellEl->query('./value') as $valueEl) {
                    $value = $valueEl->nodeValue;

                    if ('' === $value || !$valueEl->getAttribute('class')) {
                        continue;
                    }

                    $classes = explode(' ', $valueEl->getAttribute('class'));
                    $value = $this->formatter->applyClasses($classes, $value, $formatterParams);
                    $valueEl->nodeValue = $value;
                }
            }
        }

        $stylesheetDom = new \DOMDocument('1.0');
        $stylesheetDom->load($template);
        $xsltProcessor = new \XSLTProcessor();
        $xsltProcessor->importStylesheet($stylesheetDom);
        $xsltProcessor->setParameter('', 'title', $config['title']);
        $xsltProcessor->setParameter('', 'phpbench-version', PhpBench::VERSION);
        $xsltProcessor->setParameter('', 'date', date('Y-m-d H:i:s'));
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
    public function configure(OptionsResolver $options)
    {
        $options->setDefaults([
            'title' => 'PHPBench Benchmark Results',
            'template' => __DIR__ . '/templates/html.xsl',
            'file' => null,
        ]);
    }
}
