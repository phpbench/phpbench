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

use PhpBench\Benchmark\BaselineManager;
use PHPUnit\Framework\TestCase;

/**
 * @\PhpBench\Benchmark\Metadata\Annotations\BeforeMethods({"setUp"})
 */
class BaselineManagerTest extends TestCase
{
    private $manager;

    public static $callCount = false;

    protected function setUp(): void
    {
        $this->manager = new BaselineManager();
    }

    /**
     * It should throw an exception if a baseline callable name already exists.
     *
     */
    public function testRegisterTwice()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Baseline callable "foo" has already been registered.');
        $this->manager->addBaselineCallable('foo', __CLASS__ . '::baselineExample');
        $this->manager->addBaselineCallable('foo', __CLASS__ . '::baselineExample');
    }

    /**
     * It should measure the mean time taken to execute a callable.
     *
     * @\PhpBench\Benchmark\Metadata\Annotations\Subject()
     * @\PhpBench\Benchmark\Metadata\Annotations\Iterations(100)
     */
    public function testCallable()
    {
        static::$callCount = 0;
        $this->manager->addBaselineCallable('foo', __CLASS__ . '::baselineExample');
        $this->manager->benchmark('foo', 100);
        $this->assertEquals(100, static::$callCount);
    }

    /**
     * It should throw an exception if the callable is not callable (string).
     *
     */
    public function testCallableNotCallable()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Given baseline "foo" callable "does_not_exist" is not callable.');
        $this->manager->addBaselineCallable('foo', 'does_not_exist');
        $this->manager->benchmark('foo', 100);
    }

    /**
     * It should throw an exception if the callable is not callable (object).
     *
     */
    public function testCallableNotCallableObject()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Given baseline "foo" callable "object" is not callable.');
        $this->manager->addBaselineCallable('foo', new \stdClass());
        $this->manager->benchmark('foo', 100);
    }

    public static function baselineExample($revs)
    {
        self::$callCount = $revs;
    }
}
