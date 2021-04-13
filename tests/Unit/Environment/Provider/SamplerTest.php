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

namespace PhpBench\Tests\Unit\Environment\Provider;

use PhpBench\Benchmark\SamplerManager;
use PhpBench\Environment\Information;
use PhpBench\Environment\Provider\Sampler;
use PhpBench\Tests\TestCase;

class SamplerTest extends TestCase
{
    private $manager;
    private $provider;

    protected function setUp(): void
    {
        $this->manager = $this->prophesize(SamplerManager::class);
        $this->provider = new Sampler(
            $this->manager->reveal(),
            ['one']
        );
    }

    /**
     * Provider is always applicable.
     */
    public function testIsApplicable(): void
    {
        $this->assertTrue($this->provider->isApplicable());
    }

    /**
     * It should get the sampler measurements from the baseline manager.
     */
    public function testBaselineMeasurements(): void
    {
        $this->manager->sample('one', 1000)->willReturn(10);
        $info = $this->provider->getInformation();
        $this->assertInstanceOf(Information::class, $info);
        $this->assertEquals(iterator_to_array($info), [
            'one' => 10,
        ]);
    }
}
