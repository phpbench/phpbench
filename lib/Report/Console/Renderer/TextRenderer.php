<?php

namespace PhpBench\Report\Console\Renderer;

use PhpBench\Report\Console\ObjectRenderer;
use PhpBench\Report\Console\ObjectRendererInterface;
use PhpBench\Report\Model\Text;
use Symfony\Component\Console\Output\OutputInterface;

class TextRenderer implements ObjectRendererInterface
{
    public function render(OutputInterface $output, ObjectRenderer $renderer, object $object): bool
    {
        if (!$object instanceof Text) {
            return false;
        }

        $output->writeln($object->text());

        return true;
    }
}
