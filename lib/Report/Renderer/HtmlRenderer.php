<?php

namespace PhpBench\Report\Renderer;

use PhpBench\Compat\SymfonyOptionsResolverCompat;
use Symfony\Component\Filesystem\Path;
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
    final public const PARAM_TITLE = 'title';
    final public const PARAM_PATH = 'path';

    public function __construct(private readonly OutputInterface $output, private readonly ObjectRenderer $renderer, private readonly string $cwd)
    {
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
        $outputDir = dirname((string) $outputPath);

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
