<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Extensions\Sqlite\Benchmarks;

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
        $this->addContainerExtensionClass('PhpBench\\Extensions\\Sqlite\\SqliteExtension');
        $this->setContainerConfig([
            'storage' => 'sqlite',
            'storage.sqlite.db_path' => $this->getWorkspacePath() . '/test.sqlite',
        ]);
        $this->driver = $this->getContainer()->get('storage.driver.sqlite');
    }

    public function benchStore()
    {
        static $index = 0;

        $collection = TestUtil::createCollection([
            [
                'uuid' => $index . 'a',
                'env' => [
                    'foo' => ['foo' => 'bar', 'bar' => 'foo'],
                    'bar' => ['foo' => 'bar', 'bar' => 'foo'],
                    'baz' => ['foo' => 'bar', 'bar' => 'foo'],
                    'bog' => ['foo' => 'bar', 'bar' => 'foo'],
                ],
            ],
            ['uuid' => $index . 'b'],
            ['uuid' => $index . 'c'],
            ['uuid' => $index . 'd'],
        ]);
        $this->driver->store($collection);
        $index++;
    }
}
