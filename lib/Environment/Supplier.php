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

namespace PhpBench\Environment;

/**
 * Supplies information from the current environment.
 *
 * Indidual "information" is provided by "providers", which are responsible for
 * saying if they can provide any information in the current environment, for
 * example: if there is a .git directory, then the GIT provider will return
 * some information.
 */
class Supplier
{
    /**
     * @var ProviderInterface[]
     */
    private $providers = [];

    /**
     * Add a provider.
     *
     * @param ProviderInterface $provider
     */
    public function addProvider(ProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * Return information from the current environment.
     *
     * @return Information[]
     */
    public function getInformations()
    {
        $informations = [];

        foreach ($this->providers as $provider) {
            if (false === $provider->isApplicable()) {
                continue;
            }

            $informations[] = $provider->getInformation();
        }

        return $informations;
    }
}
