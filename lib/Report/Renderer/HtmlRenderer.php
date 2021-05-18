<?php

namespace PhpBench\Report\Renderer;

use PhpBench\Registry\Config;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\RendererInterface;
use PhpBench\Template\ObjectRenderer;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;


class HtmlRenderer implements RendererInterface
{
    const PARAM_OUTPUT_DIR = 'html.output_dir';
    const PARAM_CSS_FILES = 'html.css_files';

    /**
     * @var ObjectRenderers
     */
    private $renderer;

    public function __construct(
        ObjectRenderer $renderer
    ) {
        $this->renderer = $renderer;
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setDefaults([
            self::PARAM_OUTPUT_DIR => '.phpbench/html',
            self::PARAM_CSS_FILES => [
                __DIR__ . '/../../../templates/bootstrap.min.css',
                __DIR__ . '/../../../templates/phpbench.css',
            ]
        ]);
    }

    public function render(Reports $report, Config $config): void
    {
        $this->generateReport(
            $report,
            $config[self::PARAM_OUTPUT_DIR],
            $config[self::PARAM_CSS_FILES]
        );
    }

    private function generateReport(Reports $reports, string $outputDir, array $cssPaths): void
    {
        $cssOutputPath = $outputDir . '/css';

        $this->mkdirIfNotExists($outputDir);
        $this->mkdirIfNotExists($cssOutputPath);

        $cssLinks = [];

        foreach ($cssPaths as $cssPath) {
            if (!file_exists($cssPath)) {
                throw new RuntimeException(sprintf(
                    'CSS file "%s" does not exist', $cssPath
                ));
            }
            copy($cssPath, sprintf('%s/%s', $cssOutputPath, basename($cssPath)));
            $cssLinks[] = sprintf('css/%s', basename($cssPath));
        }

        $layout = new HtmlLayout($reports, $cssLinks);
        file_put_contents($outputDir . '/index.html', $this->renderer->render($layout));
    }

    private function mkdirIfNotExists(string $outputDir): void
    {
        if (!file_exists($outputDir)) {
            if (!@mkdir($outputDir, 0777, true)) {
                throw new RuntimeException(sprintf(
                    'Could not create HTML output directory "%s"',
                    $outputDir
                ));
            }
        }
    }
}
