<?php

namespace PhpBench\Report\Console;

use PhpBench\Report\Console\Exception\CouldNotRender;
use Symfony\Component\Console\Output\OutputInterface;

class ObjectRenderer
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ObjectRendererInterface[]
     */
    private $renderers;

    public function __construct(OutputInterface $output, ObjectRendererInterface ...$renderers)
    {
        $this->output = $output;
        $this->renderers = $renderers;
    }

    public function render(object $object): void
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->render($this->output, $this, $object)) {
                return;
            }
        }

        throw new CouldNotRender(sprintf(
            'Could not render object "%s"',
            get_class($object)
        ));
    }
}
