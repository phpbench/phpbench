<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Storage\Driver;

use PhpBench\Extensions\Sqlite\Storage\Driver\SqliteDriver;

class SqliteDriverTest extends \PHPUnit_Framework_TestCase
{
    private $loader;
    private $persister;
    private $repository;
    private $constraint;
    private $driver;

    public function setUp()
    {
        $this->loader = $this->prophesize('PhpBench\Extensions\Sqlite\Storage\Driver\Sqlite\Loader');
        $this->persister = $this->prophesize('PhpBench\Extensions\Sqlite\Storage\Driver\Sqlite\Persister');
        $this->repository = $this->prophesize('PhpBench\Extensions\Sqlite\Storage\Driver\Sqlite\Repository');

        $this->constraint = $this->prophesize('PhpBench\Expression\Constraint\Constraint');
        $this->suiteCollection = $this->prophesize('PhpBench\Model\SuiteCollection');

        $this->driver = new SqliteDriver(
            $this->loader->reveal(),
            $this->persister->reveal(),
            $this->repository->reveal()
        );
    }

    /**
     * It should return a SuiteCollection for a given query.
     */
    public function testQuery()
    {
        $this->loader->load(
            $this->constraint->reveal()
        )->willReturn($this->suiteCollection->reveal());

        $collection = $this->driver->query($this->constraint->reveal());
        $this->assertSame($this->suiteCollection->reveal(), $collection);
    }

    /**
     * It should store a suite collection.
     */
    public function testStore()
    {
        $this->persister->persist($this->suiteCollection->reveal())->shouldBeCalled();
        $this->driver->store($this->suiteCollection->reveal());
    }

    /**
     * It should return a history iterator.
     */
    public function testHistory()
    {
        $iterator = $this->driver->history();
        $this->assertInstanceOf('PhpBench\Extensions\Sqlite\Storage\Driver\Sqlite\HistoryIterator', $iterator);
    }
}
