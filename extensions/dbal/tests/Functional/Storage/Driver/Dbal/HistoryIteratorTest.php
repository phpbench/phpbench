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

use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\HistoryIterator;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Persister;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Repository;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Visitor\TokenValueVisitor;
use PhpBench\Extensions\Dbal\Tests\Functional\DbalTestCase;
use PhpBench\Model\SuiteCollection;
use PhpBench\Tests\Util\TestUtil;

class HistoryIteratorTest extends DbalTestCase
{
    private $persister;
    private $iterator;

    public function setUp()
    {
        $manager = $this->getManager();
        $tokenVisitor = $this->prophesize(TokenValueVisitor::class);
        $repository = new Repository($manager, $tokenVisitor->reveal());
        $this->persister = new Persister($manager);

        $this->iterator = new HistoryIterator($repository);
    }

    public function tearDown()
    {
        $this->cleanWorkspace();
    }

    /**
     * It should iterate over the history.
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

        $current = $this->iterator->current();
        $this->assertInstanceOf('PhpBench\Storage\HistoryEntry', $current);
        $this->assertEquals('2016-01-01', $current->getDate()->format('Y-m-d'));
        $this->assertEquals('branch_1', $current->getVcsBranch());
        $this->assertEquals('one', $current->getTag());
        $this->assertEquals(1, $current->getRunId());

        $this->iterator->next();
        $current = $this->iterator->current();
        $this->assertInstanceOf('PhpBench\Storage\HistoryEntry', $current);
        $this->assertEquals('2015-01-01', $current->getDate()->format('Y-m-d'));
        $this->assertEquals('branch_2', $current->getVcsBranch());
        $this->assertEquals('two', $current->getTag());
        $this->assertEquals(2, $current->getRunId());
    }
}
