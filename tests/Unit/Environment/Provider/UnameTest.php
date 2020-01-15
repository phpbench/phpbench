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

use PhpBench\Environment\Provider;
use PHPUnit\Framework\TestCase;

class UnameTest extends TestCase
{
    private $provider;

    protected function setUp(): void
    {
        $this->provider = new Provider\Uname();
    }

    /**
     * Provider is always applicable.
     */
    public function testIsApplicable()
    {
        $this->assertTrue($this->provider->isApplicable());
    }

    /**
     * It should provide the UNAME version.
     */
    public function testUnameVersion()
    {
        $info = $this->provider->getInformation();

        foreach ([
            'os' => 's',
            'host' => 'n',
            'release' => 'r',
            'version' => 'v',
            'machine' => 'm',
        ] as $key => $mode) {
            $this->assertEquals(php_uname($mode), $info[$key]);
        }
    }
}
