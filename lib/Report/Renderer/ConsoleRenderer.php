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

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Printer;
use PhpBench\Registry\Config;
use PhpBench\Report\Console\ObjectRenderer;
use PhpBench\Report\Console\ObjectRendererInterface;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\Model\Table as PhpBenchTable;
use PhpBench\Report\Model\TableRow;
use PhpBench\Report\RendererInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
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
    }
}
