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

namespace PhpBench\Extensions\Dbal\Tests\Functional\Storage\Driver\Dbal;

use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Loader;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Persister;
use PhpBench\Extensions\Dbal\Tests\Functional\DbalTestCase;
use PhpBench\Model\SuiteCollection;
use PhpBench\PhpBench;
use PhpBench\Tests\Util\TestUtil;

class PeristerTest extends DbalTestCase
{
    private $persister;
    private $manager;

    public function setUp()
    {
        $this->manager = $this->getManager();

        // instantiate persister
        $this->persister = new Persister($this->manager);
    }

    public function tearDown()
    {
        $this->cleanWorkspace();
    }

    /**
     * The persister should persist the collection to an Dbal database.
     * The loader should load the collection from an Dbal database.
     * The collection retrieved from the loader should be equal to the collection
     * passed to the persister.
     */
    public function testPersist()
    {
        $suiteCollection = new SuiteCollection([
            TestUtil::createSuite([
                'uuid' => '1',
                'subjects' => ['benchOne', 'benchTwo'],
                'groups' => ['one', 'two'],
                'parameters' => [
                    'one' => 'two',
                    'three' => ['one', 'two'],
                ],
                'env' => [
                    'system' => [
                        'os' => 'linux',
                        'memory' => 8096,
                        'distribution' => 'debian',
                    ],
                    'vcs' => [
                        'system' => 'git',
                        'branch' => 'foo',
                    ],
                ],
            ]),
            TestUtil::createSuite([
                'uuid' => '2',
                'subjects' => ['benchThree'],
                'groups' => ['five'],
            ]),
        ]);

        $this->persister->persist($suiteCollection);

        $this->assertEquals(2, $this->sqlCount('SELECT * FROM run'));
        $this->assertEquals(3, $this->sqlCount('SELECT * FROM subject'));
        $this->assertEquals(6, $this->sqlCount('SELECT * FROM iteration'));
        $this->assertEquals(5, $this->sqlCount('SELECT * FROM environment'));
        $this->assertEquals(5, $this->sqlCount('SELECT * FROM sgroup_subject'));
        $this->assertEquals(3, $this->sqlCount('SELECT * FROM parameter'));
    }

    /**
     * The PHPBench version should be stored in the database.
     */
    public function testPhpBenchVersion()
    {
        $suiteCollection = new SuiteCollection([
            TestUtil::createSuite([
                'uuid' => 1,
            ]),
        ]);

        $this->persister->persist($suiteCollection);
        $rows = $this->sqlQuery('SELECT * FROM version');
        $this->assertCount(1, $rows);
        $row = current($rows);
        $this->assertEquals(PhpBench::VERSION, $row['phpbench_version']);

        $suiteCollection = new SuiteCollection([
            TestUtil::createSuite([
                'uuid' => 2,
            ]),
        ]);
        $this->persister->persist($suiteCollection);
        $this->assertEquals(1, $this->sqlCount('SELECT * FROM version'));
    }
}
