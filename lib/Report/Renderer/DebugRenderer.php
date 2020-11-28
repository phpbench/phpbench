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

use PhpBench\Dom\Document;
use PhpBench\Registry\Config;
use PhpBench\Report\RendererInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DebugRenderer implements RendererInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function render(Document $reportsDocument, Config $config): void
    {
        $this->output->writeln('Report XML (debug):');
        $this->output->writeln($reportsDocument->dump());
    }

    public function configure(OptionsResolver $options): void
    {
    }
}
