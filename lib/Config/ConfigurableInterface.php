<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Config;

interface ConfigurableInterface
{
    /**
     * Return a JSON schema which should be used to validate the configuration.
     * Return an empty array() if you want to allow anything.
     *
     * @param OptionsResolver $options
     */
    public function getSchema();

    /***
     * Return the default configuration. This configuration will be prepended
     * to all subsequent reports and should be used to provide default values.
     *
     * @return array
     */

    public function getDefaultConfig();
}
