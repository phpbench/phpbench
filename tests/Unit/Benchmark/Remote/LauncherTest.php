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

use InvalidArgumentException;
use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Benchmark\Remote\Payload;
use PhpBench\Benchmark\Remote\PayloadFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\ExecutableFinder;

class LauncherTest extends TestCase
{
    private $factory;
    private $payload;
    private $finder;

    protected function setUp(): void
    {
        $this->factory = $this->prophesize(PayloadFactory::class);
        $this->payload = $this->prophesize(Payload::class);
        $this->finder = $this->prophesize(ExecutableFinder::class);
    }

    /**
     * It should generate a script from a given template, launch it
     * and return the results.
     */
    public function testLiveExecute()
    {
        $launcher = $this->createLiveLauncher();
        $result = $launcher->payload(
            __DIR__ . '/template/foo.template',
            [
                'foo' => 'bar',
            ]
        )->launch();

        $this->assertEquals([
            'foo' => 'bar',
        ], $result);
    }

    /**
     * It should pass the wrapper, ini settings and php-binary to the payload.
     */
    public function testPassSettingsToPayload()
    {
        $launcher = new Launcher(
            $this->factory->reveal(),
            $this->finder->reveal(),
            $bootstrap = __DIR__ . '/../../../../vendor/autoload.php',
            '/path/to/php',
            $phpConfig = ['setting_1' => 'value_1', 'setting_2' => 'value_2'],
            'wrapper'
        );

        $this->factory->create(
            __FILE__,
            ['bootstrap' => $bootstrap],
            '/path/to/php',
            null
        )->willReturn($this->payload->reveal());

        $this->payload->setWrapper('wrapper')->shouldBeCalled();
        $this->payload->mergePhpConfig($phpConfig)->shouldBeCalled();

        $launcher->payload(__FILE__, []);
    }

    /**
     * It should throw an exception if the bootstrap file does not exist.
     *
     */
    public function testInvalidBootstrap()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bootstrap file');
        $launcher = new Launcher(
            $this->factory->reveal(),
            $this->finder->reveal(),
            __DIR__ . '/../../../../vendor/notexisting.php'
        );
        $launcher->payload(
            __DIR__ . '/template/foo.template',
            [
                'foo' => 'bar',
            ]
        );
    }

    private function createLiveLauncher()
    {
        return new Launcher(
            new PayloadFactory(),
            new ExecutableFinder(),
            __DIR__ . '/../../../../vendor/autoload.php'
        );
    }
}
