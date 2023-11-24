<?php

namespace PhpBench\Tests\Unit\Report\Generator;

use PhpBench\DependencyInjection\Container;
use PhpBench\Report\Generator\BareGenerator;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Report\Transform\SuiteCollectionTransformer;

class BareGeneratorTest extends GeneratorTestCase
{
    protected static function acceptanceSubPath(): string
    {
        return 'bare';
    }

    protected function createGenerator(Container $container): GeneratorInterface
    {
        return new BareGenerator(
            new SuiteCollectionTransformer()
        );
    }
}
