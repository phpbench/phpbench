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

namespace PhpBench\Tests\Unit\Vcs\Detector;

use PhpBench\Environment\Information;
use PhpBench\Environment\ProviderInterface;
use PhpBench\Environment\Supplier;
use PHPUnit\Framework\TestCase;

class SupplierTest extends TestCase
{
    private $supplier;

    protected function setUp(): void
    {
        $this->supplier = new Supplier();
        $this->provider1 = $this->prophesize(ProviderInterface::class);
        $this->provider2 = $this->prophesize(ProviderInterface::class);
        $this->information1 = $this->prophesize(Information::class);
        $this->information2 = $this->prophesize(Information::class);
    }

    /**
     * It should return nothing if all the providers are not applicable.
     */
    public function testNoApplicableProviders()
    {
        $this->supplier->addProvider($this->provider1->reveal());
        $this->supplier->addProvider($this->provider2->reveal());
        $this->provider1->isApplicable()->willReturn(false);
        $this->provider2->isApplicable()->willReturn(false);

        $result = $this->supplier->getInformations();

        $this->assertEquals([], $result);
    }

    /**
     * It should return information from both the providers.
     */
    public function testApplicableProviders()
    {
        $this->supplier->addProvider($this->provider1->reveal());
        $this->supplier->addProvider($this->provider2->reveal());
        $this->provider1->isApplicable()->willReturn(true);
        $this->provider2->isApplicable()->willReturn(true);
        $this->provider1->getInformation()->willReturn($this->information1->reveal());
        $this->provider2->getInformation()->willReturn($this->information2->reveal());

        $result = $this->supplier->getInformations();

        $this->assertSame([
            $this->information1->reveal(),
            $this->information2->reveal(),
        ], $result);
    }
}
