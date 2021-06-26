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

use PhpBench\Compat\SymfonyOptionsResolverCompat;
use PhpBench\Registry\Config;
use PhpBench\Report\Console\ObjectRenderer;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\RendererInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsoleRenderer implements RendererInterface
{
    /**
     * @var ObjectRenderer
     */
    private $renderer;

    public function __construct(ObjectRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Render the table.
     *
     */
    public function render(Reports $reports, Config $config): void
    {
        $this->renderer->render($reports);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setDefault('table_style', null);
        $options->setAllowedTypes('table_style', ['null','scalar']);
        SymfonyOptionsResolverCompat::setInfos($options, [
            'table_style' => 'This is option does nothing and will be removed in PHPBench 2.0',
        ]);
    }
}
