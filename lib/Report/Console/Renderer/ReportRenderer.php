<?php

namespace PhpBench\Report\Console\Renderer;

use PhpBench\Report\Console\ObjectRenderer;
use PhpBench\Report\Console\ObjectRendererInterface;
use PhpBench\Report\Model\Report;
use Symfony\Component\Console\Output\OutputInterface;

class ReportRenderer implements ObjectRendererInterface
{
    public function render(OutputInterface $output, ObjectRenderer $renderer, object $object): bool
    {
        if (!$object instanceof Report) {
            return false;
        }

        if ($title = $object->title()) {
            $output->writeln(sprintf('<title>%s</title>', $title));
            $output->writeln(sprintf('<title>%s</title>', str_repeat('=', strlen($title))));
            $output->write(PHP_EOL);
        }

        if ($description = $object->description()) {
            $output->writeln(sprintf('<description>%s</description>', $description));
            $output->writeln('');
        }

        foreach ($object->objects() as $object) {
            $renderer->render($object);
        }

        return true;
    }
}
