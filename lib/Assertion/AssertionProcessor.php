<?php

namespace PhpBench\Assertion;

use Symfony\Component\OptionsResolver\OptionsResolver;
use PhpBench\Registry\Config;
use PhpBench\Math\Distribution;
use PhpBench\Json\JsonDecoder;
use PhpBench\Benchmark\Metadata\AssertionMetadata;

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

    public function assertWith($asserterName, array $config, Distribution $distribution)
    {
        $asserter = $this->registry->getService($asserterName);
        $optionsResolver = new OptionsResolver();
        $asserter->configure($optionsResolver);
        $config = new Config('test', $optionsResolver->resolve($config));

        return $asserter->assert($distribution, $config);
    }

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

