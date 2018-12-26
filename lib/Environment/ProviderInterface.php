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
 * Implementors of this interface provide information about
 * a specific feature of the environment, e.g. VCS, OS, etc.
 */
interface ProviderInterface
{
    /**
     * Return true if the instance detects a VCS repository
     * in the current CWD.
     *
     * @return bool
     */
    public function isApplicable();

    /**
     * Return information about the detected VCS repository.
     *
     * @return Information
     */
    public function getInformation();
}
