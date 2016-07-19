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

namespace PhpBench\Extensions\Dbal\Benchmarks;

use PhpBench\Benchmarks\Macro\BaseBenchCase;
use PhpBench\Tests\Util\TestUtil;

/**
 * @OutputTimeUnit("milliseconds", precision=2)
 * @Revs(1)
 */
class StorageBench extends BaseBenchCase
{
    private $driver;

    public function __construct()
    {
        $this->addContainerExtensionClass('PhpBench\\Extensions\\Dbal\\DbalExtension');
        $this->setContainerConfig([
            'storage' => 'dbal',
            'path' => $this->getWorkspacePath() . '/test.sqlite',
        ]);
        $this->driver = $this->getContainer()->get('storage.driver.dbal');
    }

    public function benchStore()
    {
        $uuid = uniqid();
        $collection = TestUtil::createCollection([
            [
                'uuid' => $uuid . 'a',
                'env' => [
                    'foo' => ['foo' => 'bar', 'bar' => 'foo'],
                    'bar' => ['foo' => 'bar', 'bar' => 'foo'],
                    'baz' => ['foo' => 'bar', 'bar' => 'foo'],
                    'bog' => ['foo' => 'bar', 'bar' => 'foo'],
                ],
            ],
            ['uuid' => $uuid . 'b'],
            ['uuid' => $uuid . 'c'],
            ['uuid' => $uuid . 'd'],
        ]);
        $this->driver->store($collection);
        $uuid++;
    }

    public function benchStoreParams()
    {
        $uuid = uniqid();
        $collection = TestUtil::createCollection([
            [
                'uuid' => $uuid . 'a',
                'parameters' => [
                    'one' => 'two',
                    'three' => 'four',
                    'two' => 'five',
                    '7' => 'eight',
                ],
                'env' => [
                    'foo' => ['foo' => 'bar', 'bar' => 'foo'],
                    'bar' => ['foo' => 'bar', 'bar' => 'foo'],
                    'baz' => ['foo' => 'bar', 'bar' => 'foo'],
                    'bog' => ['foo' => 'bar', 'bar' => 'foo'],
                ],
            ],
            ['uuid' => $uuid . 'b'],
            ['uuid' => $uuid . 'c'],
            ['uuid' => $uuid . 'd'],
        ]);
        $this->driver->store($collection);
        $uuid++;
    }
}
