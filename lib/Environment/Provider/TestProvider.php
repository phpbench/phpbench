<?php

namespace PhpBench\Environment\Provider;

use PhpBench\Environment\Information;
use PhpBench\Environment\ProviderInterface;

class TestProvider implements ProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function isApplicable(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getInformation(): Information
    {
        return new Information('test', [
            'example1' => 1,
            'example2' => 2,
        ]);
    }
}
