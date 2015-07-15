<?php

namespace PhpBench\Report\Generator;

use PhpBench\Console\OutputAware;
use PhpBench\Result\Dumper\XmlDumper;
use Symfony\Component\Console\Helper\Table;
use PhpBench\ReportGenerator;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Console\Output\OutputInterface;
use PhpBench\Result\SuiteResult;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use PhpBench\Report\Dom\PhpBenchXpath;

class ConsoleTableGenerator implements OutputAware
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var XmlDumper
     */
    private $xmlDumper;

    public function __construct(XmlDumper $xmlDumper = null)
    {
        $this->xmlDumper = $xmlDumper ? $xmlDumper : new XmlDumper();
    }

    public function configure(OptionsResolver $options)
    {
        $options->setDefaults(array(
            'title' => null,
            'description' => null,
            'selector' => '//iteration',
            'headers' => array(),
            'cells' => array(),
        ));
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
        $this->configureFormatters($output->getFormatter());
    }

    public function generate(SuiteResult $suite, array $config)
    {
        if (null !== $config['title']) {
            $this->output->writeln(sprintf('<title>%s</title>', $config['title']));
        }

        if (null !== $config['description']) {
            $this->output->writeln(sprintf('<description>%s</description>', $config['description']));
        }

        $dom = $this->xmlDumper->dump($suite);
        $xpath = new PhpBenchXpath($dom);
        $table = new Table($this->output);
        $table->setHeaders($config['headers']);

        foreach ($xpath->query($config['selector']) as $rowEl) {
            $row = array();
            foreach ($config['cells'] as $colName => $cellExpr) {
                $value = $xpath->evaluate($cellExpr, $rowEl);

                if (!is_scalar($value)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Expected XPath expression "%s" to evaluate to a scalar, got "%s"',
                        $cellExpr, is_object($value) ? get_class($value) : gettype($value)
                    ));
                }
                $row[$colName] = $value;
            }

            $table->addRow($row);
        }

        $table->render();
    }

    private function configureFormatters(OutputFormatterInterface $formatter)
    {
        $formatter->setStyle(
            'title', new OutputFormatterStyle('white', 'blue', array('bold'))
        );
        $formatter->setStyle(
            'description', new OutputFormatterStyle(null, null, array())
        );
    }
}
