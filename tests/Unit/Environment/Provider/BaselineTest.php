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

use PhpBench\Benchmark\BaselineManager;
use PhpBench\Environment\Information;
use PhpBench\Environment\Provider\Baseline;
use PHPUnit\Framework\TestCase;

class BaselineTest extends TestCase
{
    private $manager;
    private $provider;

    protected function setUp(): void
    {
        $this->manager = $this->prophesize(BaselineManager::class);
        $this->provider = new Baseline(
            $this->manager->reveal(),
            ['one']
        );
    }

    /**
     * Provider is always applicable.
     */
    public function testIsApplicable()
    {
        $this->assertTrue($this->provider->isApplicable());
    }

    /**
     * It should get the baseline measurements from the baseline manager.
     */
    public function testBaselineMeasurements()
    {
        $this->manager->benchmark('one', 1000)->willReturn(10);
        $info = $this->provider->getInformation();
        $this->assertInstanceOf(Information::class, $info);
        $this->assertEquals(iterator_to_array($info), [
            'one' => 10,
        ]);
    }
}
