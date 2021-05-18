<?php

namespace PhpBench\Report\Renderer;

use PhpBench\Registry\Config;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\RendererInterface;
use PhpBench\Template\ObjectPathResolver;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemplateRenderer implements RendererInterface
{
    /**
     * @var ObjectPathResolver
     */
    private $pathResolver;

    public function __construct(ObjectPathResolver $pathResolver)
    {
        $this->pathResolver = $pathResolver;
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
    }

    public function render(Reports $report, Config $config)
    {
    }
}
