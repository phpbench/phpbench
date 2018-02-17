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

use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Persister;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Repository;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Schema;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Visitor\TokenValueVisitor;
use PhpBench\Extensions\Dbal\Tests\Functional\DbalTestCase;
use PhpBench\Model\SuiteCollection;
use PhpBench\Tests\Util\TestUtil;

class RepositoryTest extends DbalTestCase
{
    private $persister;
    private $repository;

    public function setUp()
    {
        $manager = $this->getManager();

        $this->persister = new Persister($manager);
        $tokenVisitor = $this->prophesize(TokenValueVisitor::class);
        $this->repository = new Repository($manager, $tokenVisitor->reveal());
    }

    /**
     * It should return the history statement.
     */
    public function testHistoryStatement()
    {
        $suiteCollection = new SuiteCollection([
            TestUtil::createSuite([
                'uuid' => 1,
                'env' => [
                    'vcs' => [
                        'system' => 'git',
                        'branch' => 'branch_1',
                    ],
                ],
                'name' => 'one',
                'date' => '2016-01-01',
            ]),
            TestUtil::createSuite([
                'uuid' => 2,
                'date' => '2015-01-01',
                'env' => [
                    'vcs' => [
                        'system' => 'git',
                        'branch' => 'branch_2',
                    ],
                ],
                'name' => 'two',
            ]),
        ]);

        $this->persister->persist($suiteCollection);
        $statement = $this->repository->getHistoryStatement();
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $this->assertEquals([
            [
                'run_date' => '2016-01-01 00:00:00',
                'tag' => 'one',
                'vcs_branch' => 'branch_1',
                'run_uuid' => 1,
                'nb_subjects' => '1',
                'nb_iterations' => '2',
                'nb_revolutions' => '5',
                'min_time' => '2.0',
                'max_time' => '4.0',
                'mean_time' => '3.0',
                'mean_rstdev' => '33.333333333333',
                'total_time' => '6.0',
            ],
            [
                'run_date' => '2015-01-01 00:00:00',
                'tag' => 'two',
                'vcs_branch' => 'branch_2',
                'run_uuid' => 2,
                'nb_subjects' => '1',
                'nb_iterations' => '2',
                'nb_revolutions' => '5',
                'min_time' => '2.0',
                'max_time' => '4.0',
                'mean_time' => '3.0',
                'mean_rstdev' => '33.333333333333',
                'total_time' => '6.0',
            ],
        ], $rows);
    }

    /**
     * It should get parameters.
     */
    public function testParameters()
    {
        $parameters = [
            'one' => 'two',
            'two' => ['three', 'four'],
        ];

        $suiteCollection = new SuiteCollection([
            TestUtil::createSuite([
                'uuid' => 1234,
                'parameters' => $parameters,
            ]),
        ]);

        $this->persister->persist($suiteCollection);
        $params = $this->repository->getParameters(1);

        $this->assertEquals($parameters, $params);
    }

    /**
     * It should retrieve the latest suite UUID.
     */
    public function testLastestSuiteUuid()
    {
        $suiteCollection = new SuiteCollection([
            TestUtil::createSuite([
                'uuid' => 50,
            ]),
            TestUtil::createSuite([
                'uuid' => 5,
            ]),
            TestUtil::createSuite([
                'uuid' => 500,
            ]),
            TestUtil::createSuite([
                'uuid' => 7,
            ]),
        ]);

        $this->persister->persist($suiteCollection);
        $latestUuid = $this->repository->getLatestRunUuid();

        $this->assertEquals(7, $latestUuid);
    }

    /**
     * It can determine if run exists.
     */
    public function testHasRun()
    {
        $suiteCollection = new SuiteCollection([
            TestUtil::createSuite([
                'uuid' => 1234,
            ]),
        ]);

        $this->persister->persist($suiteCollection);

        $this->assertTrue($this->repository->hasRun(1234));
        $this->assertFalse($this->repository->hasRun(4321));
    }

    /**
     * It should delete a specified run and all of its relations.
     */
    public function testDelete()
    {
        $suiteCollection = new SuiteCollection([
            TestUtil::createSuite([
                'uuid' => 1234,
                'subjects' => ['one', 'two'],
                'env' => [
                    'system' => [
                        'os' => 'linux',
                        'distribution' => 'debian',
                    ],
                ],
            ]),
        ]);

        $this->persister->persist($suiteCollection);

        $counts = $this->getTableCounts();
        $this->assertEquals([
            'run' => 1,
            'subject' => 2,
            'variant' => 2,
            'parameter' => 1,
            'variant_parameter' => 2,
            'sgroup_subject' => 6,
            'environment' => 2,
            'iteration' => 4,
            'version' => 1,
        ], $counts);

        $this->repository->deleteRun(1234);

        $counts = $this->getTableCounts();
        $this->assertEquals([
            'run' => 0,
            'subject' => 2,
            'variant' => 0,
            'parameter' => 1,
            'variant_parameter' => 0,
            'sgroup_subject' => 6,
            'environment' => 0,
            'iteration' => 0,
            'version' => 1,
        ], $counts);
    }

    private function getTableCounts()
    {
        $schema = new Schema();
        $counts = [];
        $conn = $this->getConnection();
        foreach ($schema->getTables() as $table) {
            $count = $conn->query('SELECT COUNT(*) FROM ' . $table->getName());
            $count = (int) $count->fetchColumn(0);
            $counts[$table->getName()] = $count;
        }

        return $counts;
    }
}
