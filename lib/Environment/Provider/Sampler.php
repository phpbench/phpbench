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

namespace PhpBench\Environment\Provider;

use PhpBench\Benchmark\SamplerManager;
use PhpBench\Environment\Information;
use PhpBench\Environment\ProviderInterface;

/**
 * Runs basic micro-benchmarks via. the BaselineManager to determine some baseline
 * characteristics of the underlying system under test.
 */
class Sampler implements ProviderInterface
{
    /**
     * @var SamplerManager
     */
    private $manager;

    private $enabled = [];

    public function __construct(SamplerManager $manager, array $enabled)
    {
        $this->manager = $manager;
        $this->enabled = $enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getInformation(): Information
    {
        $results = [];

        foreach ($this->enabled as $callbackName) {
            $results[$callbackName] = $this->manager->sample($callbackName, 1000);
        }

        return new Information(
            'sampler',
            $results
        );
    }
}
