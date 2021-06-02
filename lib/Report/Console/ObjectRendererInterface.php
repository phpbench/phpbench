<?php

namespace PhpBench\Report\Console;

use PhpBench\Report\Console\Exception\CouldNotRender;
use Symfony\Component\Console\Output\OutputInterface;

interface ObjectRendererInterface
{
    public function render(OutputInterface $output, ObjectRenderer $renderer, object $object): bool;
}
