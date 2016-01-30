<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Functional\Report\Generator;

use PhpBench\Dom\Document;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Tests\Functional\FunctionalTestCase;

abstract class GeneratorTestCase extends FunctionalTestCase
{
    abstract protected function getGenerator();

    protected function getConfig(array $config)
    {
        return new Config('test', array_merge(
            $this->getGenerator()->getDefaultConfig(),
            $config
        ));
    }

    protected function generate(SuiteCollection $collection, $config)
    {
        return $this->getGenerator()->generate(
            $collection,
            $this->getConfig($config)
        );
    }

    protected function assertXPathEvaluation(Document $dom, $expected, $expr)
    {
        $result = $dom->evaluate($expr);
        $this->assertEquals($expected, $result, $expr);
    }
}
