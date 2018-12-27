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

use PhpBench\Benchmark\BaselineManager;
use PhpBench\Environment\Information;
use PhpBench\Environment\ProviderInterface;

/**
 * Runs basic micro-benchmarks via. the BaselineManager to determine some baseline
 * characteristics of the underlying system under test.
 */
class Baseline implements ProviderInterface
{
    private $manager;
    private $enabled = [];

    public function __construct(BaselineManager $manager, array $enabled)
    {
        $this->manager = $manager;
        $this->enabled = $enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getInformation()
    {
        $results = [];

        foreach ($this->enabled as $callbackName) {
            $results[$callbackName] = $this->manager->benchmark($callbackName, 1000);
        }

        return new Information(
            'baseline',
            $results
        );
    }
}
