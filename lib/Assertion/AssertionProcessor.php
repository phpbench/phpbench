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

namespace PhpBench\Assertion;

use PhpBench\Benchmark\Metadata\AssertionMetadata;
use PhpBench\Json\JsonDecoder;
use PhpBench\Registry\Config;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssertionProcessor
{
    /**
     * @var AsserterRegistry
     */
    private $registry;

    /**
     * @var JsonDecoder
     */
    private $jsonDecoder;

    public function __construct(AsserterRegistry $registry, JsonDecoder $jsonDecoder)
    {
        $this->registry = $registry;
        $this->jsonDecoder = $jsonDecoder;
    }

    public function assertWith($asserterName, array $config, AssertionData $data)
    {
        $asserter = $this->registry->getService($asserterName);
        $optionsResolver = new OptionsResolver();
        $asserter->configure($optionsResolver);
        $config = new Config('test', $optionsResolver->resolve($config));

        return $asserter->assert($data, $config);
    }

    /**
     * Return an array of assertion metadatas from the raw JSON-like stuff from the CLI.
     */
    public function assertionsFromRawCliConfig(array $rawAssertions)
    {
        $assertions = [];

        foreach ($rawAssertions as $rawAssertion) {
            $config = $this->jsonDecoder->decode($rawAssertion);
            $assertions[] = new AssertionMetadata($config);
        }

        return $assertions;
    }
}
