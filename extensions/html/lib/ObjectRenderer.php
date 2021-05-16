<?php

namespace PhpBench\Extensions\Html;

interface ObjectRenderer
{
    public function render(ObjectRenderers $renderer, object $object): ?string;
}
