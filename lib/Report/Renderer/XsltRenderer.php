<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Report\Renderer;

use PhpBench\Console\OutputAwareInterface;
use PhpBench\Dom\Document;
use PhpBench\PhpBench;
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
    public function render(Document $reportDom, array $config)
    {
        $template = $config['template'];
        $out = $config['file'];

        if (!file_exists($template)) {
            throw new \RuntimeException(sprintf(
                'XSLT template file "%s" does not exist',
                $template
            ));
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

        if (null !== $out) {
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
        return array(
            'title' => 'PHPBench Benchmark Results',
            'template' => __DIR__ . '/templates/html.xsl',
            'file' => self::DEFAULT_FILENAME,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return array(
            'type' => 'object',
            'properties' => array(
                'title' => array(
                    'type' => 'string',
                ),
                'template' => array(
                    'type' => 'string',
                ),
                'file' => array(
                    'type' => ['string', 'null'],
                ),
            ),
            'additionalProperties' => false,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOutputs()
    {
        return array(
            'html' => array(
                'template' => __DIR__ . '/templates/html.xsl',
                'file' => null,
            ),
            'markdown' => array(
                'template' => __DIR__ . '/templates/markdown.xsl',
                'file' => null,
            ),
        );
    }
}
