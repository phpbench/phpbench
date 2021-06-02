<?php

namespace PhpBench\Report\Renderer;

use PhpBench\Compat\SymfonyOptionsResolverCompat;
use PhpBench\Registry\Config;
use PhpBench\Report\Model\HtmlDocument;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\RendererInterface;
use PhpBench\Template\ObjectRenderer;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HtmlRenderer implements RendererInterface
{
    private const PARAM_TITLE = 'title';

    /**
     * @var ObjectRenderer
     */
    private $renderer;

    /**
     * @var string
     */
    private $outputDir;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output, ObjectRenderer $renderer, string $outputDir)
    {
        $this->output = $output;
        $this->renderer = $renderer;
        $this->outputDir = $outputDir;
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setDefaults([
            self::PARAM_TITLE => 'PHPBench Report',
        ]);
        $options->setAllowedTypes(self::PARAM_TITLE, ['string']);
        SymfonyOptionsResolverCompat::setInfos($options, [
            self::PARAM_TITLE => 'Title of document',
        ]);
    }

    public function render(Reports $report, Config $config): void
    {
        if (!file_exists($this->outputDir)) {
            if (!@mkdir($this->outputDir, 0777, true)) {
                throw new RuntimeException(sprintf(
                    'Could not create directory "%s"',
                    $this->outputDir
                ));
            }
        }

        $rendered = $this->renderer->render(new HtmlDocument($config[self::PARAM_TITLE], $report));
        $outPath = $this->outputDir . '/index.html';

        if (false === file_put_contents($outPath, $rendered)) {
            throw new RuntimeException(sprintf(
                'Could not write report to file "%s"',
                $outPath
            ));
        }

        $this->output->writeln(sprintf('Written report to: %s', $outPath));
    }
}
