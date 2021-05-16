<?php

namespace PhpBench\Extensions\Html;

use RuntimeException;

class ObjectRenderers
{
    /**
     * @var array
     */
    private $renderers;

    public function __construct(array $renderers)
    {
        $this->renderers = $renderers;
    }

    public function render(object $object): string
    {
        foreach ($this->renderers as $renderer) {
            $rendered = $renderer->render($this, $object);
            if (null === $rendered) {
                continue;
            }

            return $rendered;
        }

        throw new RuntimeException(sprintf(
            'Could not find object renderer for "%s"',
            get_class($object)
        ));
    }
}
