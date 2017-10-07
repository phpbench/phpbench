<?php

namespace PhpBench\Assertion;

use Symfony\Component\OptionsResolver\OptionsResolver;
use PhpBench\Registry\Config;
use PhpBench\Math\Distribution;

class Assertion
{
    /**
     * @var AsserterRegistry
     */
    private $registry;

    public function __construct(AsserterRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function assertWith($asserterName, array $config, Distribution $distribution)
    {
        $asserter = $this->registry->getService($asserterName);
        $optionsResolver = new OptionsResolver();
        $asserter->configure($optionsResolver);
        $config = new Config('test', $optionsResolver->resolve($config));

        return $asserter->assert($distribution, $config);
    }
}

