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

use PhpBench\Benchmark\SamplerManager;
use PhpBench\Tests\TestCase;

/**
 * @\PhpBench\Benchmark\Metadata\Annotations\BeforeMethods({"setUp"})
 */
class SamplerManagerTest extends TestCase
{
    private $manager;

    public static $callCount = false;

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
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Baseline callable "foo" has already been registered.');
        $this->manager->addSamplerCallable('foo', __CLASS__ . '::samplerExample');
        $this->manager->addSamplerCallable('foo', __CLASS__ . '::samplerExample');
    }

    /**
     * It should measure the mean time taken to execute a callable.
     *
     * @\PhpBench\Benchmark\Metadata\Annotations\Subject()
     *
     * @\PhpBench\Benchmark\Metadata\Annotations\Iterations(100)
     */
    public function testCallable(): void
    {
        static::$callCount = 0;
        $this->manager->addSamplerCallable('foo', __CLASS__ . '::samplerExample');
        $this->manager->sample('foo', 100);
        $this->assertEquals(100, static::$callCount);
    }

    /**
     * It should throw an exception if the callable is not callable (string).
     *
     */
    public function testCallableNotCallable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Given sampler "foo" callable "does_not_exist" is not callable.');
        $this->manager->addSamplerCallable('foo', 'does_not_exist');
        $this->manager->sample('foo', 100);
    }

    /**
     * It should throw an exception if the callable is not callable (object).
     *
     */
    public function testCallableNotCallableObject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Given sampler "foo" callable "object" is not callable.');
        $this->manager->addSamplerCallable('foo', new \stdClass());
        $this->manager->sample('foo', 100);
    }

    public static function samplerExample($revs): void
    {
        self::$callCount = $revs;
    }
}
