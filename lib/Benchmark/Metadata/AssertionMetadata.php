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

namespace PhpBench\Benchmark\Metadata;

/**
 * @deprecated
 *
 * @todo seems this class is not used so we can drop it
 */
class AssertionMetadata
{
    /**
     * @param mixed[] $config
     */
    public function __construct(private readonly array $config)
    {
    }

    /**
     * @return mixed[]
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
