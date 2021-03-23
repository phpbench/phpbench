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

namespace PhpBench\Tests\Unit\Report\Renderer;

use PhpBench\Registry\Config;
use PhpBench\Report\Generator\OutputTestGenerator;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\RendererInterface;
use PhpBench\Tests\IntegrationTestCase;
use PhpBench\Tests\Util\TestUtil;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractRendererCase extends IntegrationTestCase
{
    abstract protected function getRenderer(): RendererInterface;

    /**
     * @param parameters $config
     */
    protected function renderReport($reports, $config): void
    {
        $renderer = $this->getRenderer();
        $options = new OptionsResolver();
        $renderer->configure($options);

        $renderer->render($reports, new Config('test', $options->resolve($config)));
    }

    public function reports(array $config = []): Reports
    {
        $collection = TestUtil::createCollection([]);

        return (new OutputTestGenerator())->generate($collection, new Config('foo', $config));
    }
}
