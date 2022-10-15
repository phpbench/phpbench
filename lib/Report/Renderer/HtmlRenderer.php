<?php

namespace PhpBench\Report\Renderer;

use PhpBench\Compat\SymfonyOptionsResolverCompat;
use PhpBench\Path\Path;
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
    public const PARAM_TITLE = 'title';
    public const PARAM_PATH = 'path';


    /**
     * @var ObjectRenderer
     */
    private $renderer;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var string
     */
    private $cwd;

    public function __construct(OutputInterface $output, ObjectRenderer $renderer, string $cwd)
    {
        $this->output = $output;
        $this->renderer = $renderer;
        $this->cwd = $cwd;
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setDefaults([
            self::PARAM_TITLE => 'PHPBench Report',
            self::PARAM_PATH => '.phpbench/html/index.html'
        ]);
        $options->setAllowedTypes(self::PARAM_TITLE, ['string']);
        $options->setAllowedTypes(self::PARAM_PATH, ['string']);
        SymfonyOptionsResolverCompat::setInfos($options, [
            self::PARAM_TITLE => 'Title of document',
            self::PARAM_PATH => 'Path to output document',
        ]);
    }

    public function render(Reports $report, Config $config): void
    {
        $outputPath = Path::makeAbsolute($config[self::PARAM_PATH], $this->cwd);
        $outputDir = dirname($outputPath);

        if (!file_exists($outputDir)) {
            if (!@mkdir($outputDir, 0777, true)) {
                throw new RuntimeException(sprintf(
                    'Could not create directory "%s"',
                    $outputDir
                ));
            }
        }

        $rendered = $this->renderer->render(new HtmlDocument($config[self::PARAM_TITLE], $report));

        if (false === file_put_contents($outputPath, $rendered)) {
            throw new RuntimeException(sprintf(
                'Could not write report to file "%s"',
                $outputPath
            ));
        }

        $this->output->writeln(sprintf('Written report to: %s', $outputPath));
    }
}
