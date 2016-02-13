<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Functional\Storage\Driver\Sqlite;

use PhpBench\Extensions\Sqlite\Storage\Driver\Sqlite\ConnectionManager;
use PhpBench\Extensions\Sqlite\Storage\Driver\Sqlite\Persister;
use PhpBench\Extensions\Sqlite\Storage\Driver\Sqlite\Repository;
use PhpBench\Model\SuiteCollection;
use PhpBench\Tests\Functional\FunctionalTestCase;
use PhpBench\Tests\Util\TestUtil;

class RepositoryTest extends FunctionalTestCase
{
    private $persister;
    private $repository;

    public function setUp()
    {
        $this->initWorkspace();
        $manager = new ConnectionManager($this->getWorkspacePath() . '/test.sqlite');

        $this->persister = new Persister($manager);
        $this->repository = new Repository($manager);
    }

    public function tearDown()
    {
        $this->cleanWorkspace();
    }

    /**
     * It should return the history statement.
     */
    public function testHistoryStatement()
    {
        $suiteCollection = new SuiteCollection(array(
            TestUtil::createSuite(array(
                'env' => array(
                    'vcs' => array(
                        'system' => 'git',
                        'branch' => 'branch_1',
                    ),
                ),
                'name' => 'one',
                'date' => '2016-01-01',
            )),
            TestUtil::createSuite(array(
                'date' => '2015-01-01',
                'env' => array(
                    'vcs' => array(
                        'system' => 'git',
                        'branch' => 'branch_2',
                    ),
                ),
                'name' => 'two',
            )),
        ));

        $this->persister->persist($suiteCollection);
        $statement = $this->repository->getHistoryStatement();
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $this->assertEquals(array(
            array(
                'run_date' => '2015-01-01 00:00:00',
                'context' => 'two',
                'vcs_branch' => 'branch_2',
                'run_id' => 2,
            ),
            array(
                'run_date' => '2016-01-01 00:00:00',
                'context' => 'one',
                'vcs_branch' => 'branch_1',
                'run_id' => 1,
            ),
        ), $rows);
    }

    /**
     * It should get parameters.
     */
    public function testParameters()
    {
        $parameters = array(
            'one' => 'two',
            'two' => array('three', 'four'),
        );

        $suiteCollection = new SuiteCollection(array(
            TestUtil::createSuite(array(
                'parameters' => $parameters,
            )),
        ));

        $this->persister->persist($suiteCollection);
        $params = $this->repository->getParameters(1);

        $this->assertEquals($parameters, $params);
    }
}
