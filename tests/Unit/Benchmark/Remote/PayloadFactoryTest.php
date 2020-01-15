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

namespace PhpBench\Tests\Unit\Benchmark\Remote;

use PhpBench\Benchmark\Remote\Payload;
use PhpBench\Benchmark\Remote\PayloadFactory;
use PHPUnit\Framework\TestCase;

class PayloadFactoryTest extends TestCase
{
    private $factory;

    protected function setUp(): void
    {
        $this->factory = new PayloadFactory();
    }

    /**
     * It should create a new payload.
     */
    public function testCreate()
    {
        $payload = $this->factory->create('template', ['token' => 'one'], '/path/to/php');
        $this->assertInstanceOf(Payload::class, $payload);
    }
}
