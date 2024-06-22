<?php

namespace PhpBench\Examples\Extension\Environment;

use PhpBench\Environment\Information;
use PhpBench\Environment\ProviderInterface;

class HomeProvider implements ProviderInterface
{
    public function isApplicable(): bool
    {
        // Example: this provider requires the HOME environment variable to be set.
        return (bool) getenv('HOME');
    }

    public function getInformation(): Information
    {
        // Example: return the value of the HOME environment variable.
        return new Information('home', [
            'directory' => getenv('HOME'),
        ]);
    }
}
