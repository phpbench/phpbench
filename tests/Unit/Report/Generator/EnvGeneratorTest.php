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
use PhpBench\Report\Generator\EnvGenerator;
use PhpBench\Report\GeneratorInterface;

class EnvGeneratorTest extends GeneratorTestCase
{
    protected function acceptanceSubPath(): string
    {
        return 'env';
    }

    protected function createGenerator(Container $container): GeneratorInterface
    {
        return new EnvGenerator();
    }
}
