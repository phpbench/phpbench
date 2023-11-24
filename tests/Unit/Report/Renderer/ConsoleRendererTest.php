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

use Generator;
use PhpBench\Extension\ConsoleExtension;
use PhpBench\Report\Renderer\ConsoleRenderer;
use PhpBench\Report\RendererInterface;
use PhpBench\Tests\Util\Approval;

class ConsoleRendererTest extends AbstractRendererCase
{
    protected function getRenderer(): RendererInterface
    {
        return $this->container([
            ConsoleExtension::PARAM_OUTPUT_STREAM => $this->workspace()->path('out')
        ])->get(ConsoleRenderer::class);
    }

    /**
     * @dataProvider provideRender
     */
    public function testRender(string $path): void
    {
        $approval = Approval::create($path, 2);

        $this->renderReport($this->reports(), $approval->getConfig(0));

        $approval->approve($this->workspace()->getContents('out'));
    }

    public static function provideRender(): Generator
    {
        foreach (glob(sprintf('%s/%s/*', __DIR__, 'console')) as $path) {
            yield [$path];
        }
    }
}
