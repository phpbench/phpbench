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

namespace PhpBench\Tests\Unit\Report\Generator;

use PhpBench\DependencyInjection\Container;
use PhpBench\Expression\ExpressionEvaluator;
use PhpBench\Report\ComponentGeneratorAgent;
use PhpBench\Report\Generator\ComponentGenerator;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Report\Transform\SuiteCollectionTransformer;
use Psr\Log\NullLogger;

class ComponentGeneratorTest extends GeneratorTestCase
{
    protected function acceptanceSubPath(): string
    {
        return 'component';
    }

    protected function createGenerator(Container $container): GeneratorInterface
    {
        $container = $this->container();

        return new ComponentGenerator(
            $container->get(SuiteCollectionTransformer::class),
            $container->get(ComponentGeneratorAgent::class),
            $container->get(ExpressionEvaluator::class),
            new NullLogger()
        );
    }
}
