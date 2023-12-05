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

namespace PhpBench\Tests\Unit\Benchmark;

use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use InvalidArgumentException;
use PhpBench\Benchmark\Metadata\Annotations\Subject;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use stdClass;
use PhpBench\Benchmark\SamplerManager;
use PhpBench\Tests\TestCase;

/**
 * @BeforeMethods({"setUp"})
 */
class SamplerManagerTest extends TestCase
{
    private SamplerManager $manager;

    protected function setUp(): void
    {
        $this->manager = new SamplerManager();
    }

    /**
     * It should throw an exception if a sampler callable name already exists.
     *
     */
    public function testRegisterTwice(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Baseline callable "foo" has already been registered.');
        $this->manager->addSamplerCallable('foo', fn () => null);
        $this->manager->addSamplerCallable('foo', fn () => null);
    }

    /**
     * It should measure the mean time taken to execute a callable.
     *
     * @Subject()
     *
     * @Iterations(100)
     */
    public function testCallable(): void
    {
        $callCount = 0;
        $callable = function (int $revs) use (&$callCount): void {
            $callCount = $revs;
        };

        $this->manager->addSamplerCallable('foo', $callable);
        $this->manager->sample('foo', 100);

        $this->assertEquals(100, $callCount);
    }

    /**
     * It should throw an exception if the callable is not callable (string).
     *
     */
    public function testCallableNotCallable(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Given sampler "foo" callable "does_not_exist" is not callable.');
        $this->manager->addSamplerCallable('foo', 'does_not_exist'); // @phpstan-ignore-line
        $this->manager->sample('foo', 100);
    }

    /**
     * It should throw an exception if the callable is not callable (object).
     *
     */
    public function testCallableNotCallableObject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Given sampler "foo" callable "object" is not callable.');
        $this->manager->addSamplerCallable('foo', new stdClass());
        $this->manager->sample('foo', 100);
    }
}
