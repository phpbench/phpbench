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

namespace PhpBench\Tests\Unit\Storage\Driver\Xml;

use PhpBench\Dom\Document;
use PhpBench\Model\Suite;
use PhpBench\Model\SuiteCollection;
use PhpBench\Model\Summary;
use PhpBench\Serializer\XmlDecoder;
use PhpBench\Storage\Driver\Xml\HistoryIterator;
use PhpBench\Tests\Util\Workspace;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;

class HistoryIteratorTest extends TestCase
{
    private $xmlDecoder;
    private $iterator;
    private $filesystem;

    protected function setUp(): void
    {
        Workspace::initWorkspace();

        $this->xmlDecoder = $this->prophesize(XmlDecoder::class);
        $this->iterator = new HistoryIterator(
            $this->xmlDecoder->reveal(),
            Workspace::getWorkspacePath()
        );

        $this->filesystem = new Filesystem();
    }

    /**
     * It should "iterate" given an empty directory.
     */
    public function testIterateEmpty()
    {
        $entries = iterator_to_array($this->iterator);
        $this->assertCount(0, $entries);
    }

    /**
     * It should "iterate" if the directory does not exist.
     */
    public function testNoDirectoryExist()
    {
        $iterator = new HistoryIterator(
            $this->xmlDecoder->reveal(),
            'foobar_not_exist'
        );

        $iterator->current();
        $this->addToAssertionCount(1);
    }

    /**
     * It should iterate over entries.
     */
    public function testIterate()
    {
        $collections = [];
        $collections[1] = $this->createEntry(1, new \DateTime('2014-02-03'));
        $collections[2] = $this->createEntry(2, new \DateTime('2016-01-01'));
        $collections[3] = $this->createEntry(3, new \DateTime('2016-02-01'));
        $collections[4] = $this->createEntry(4, new \DateTime('2016-02-03'));
        $collections[5] = $this->createEntry(5, new \DateTime('2016-02-08 12:00:00'));
        $collections[6] = $this->createEntry(6, new \DateTime('2016-02-08 06:00:00'));

        $this->xmlDecoder->decode(Argument::type(Document::class))->will(function ($args) use (&$order, &$collections) {
            $dom = $args[0];
            $uuid = $dom->evaluate('number(./@uuid)');

            return $collections[$uuid];
        });

        $entries = iterator_to_array($this->iterator);
        $this->assertCount(6, $entries);
        $first = array_shift($entries);
        $this->assertEquals('2016-02-08 12:00:00', $first->getDate()->format('Y-m-d H:i:s'));
        $this->assertEquals(5, $first->getNbSubjects());
        $this->assertEquals(10, $first->getNbIterations());
        $this->assertEquals(1000, $first->getNbRevolutions());
        $this->assertEquals(1, $first->getMinTime());
        $this->assertEquals(2, $first->getMaxTime());
        $this->assertEquals(3, $first->getMeanTime());
        $this->assertEquals(4, $first->getMeanRelStDev());
        $this->assertEquals(5, $first->getTotalTime());

        $last = array_pop($entries);
        $this->assertEquals('2014-02-03', $last->getDate()->format('Y-m-d'));
    }

    private function createEntry($uuid, \DateTime $date)
    {
        $dom = new Document();
        $test = $dom->createRoot('test');
        $test->setAttribute('uuid', $uuid);
        $path = Workspace::getWorkspacePath() . $date->format('/Y/m/d/') . '/' . $uuid . '.xml';

        if (!$this->filesystem->exists(dirname($path))) {
            $this->filesystem->mkdir(dirname($path));
        }

        $dom->save($path);

        return $this->createCollection($uuid, $date);
    }

    private function createCollection($uuid, \DateTime $date)
    {
        $collection = $this->prophesize(SuiteCollection::class);
        $suite = $this->prophesize(Suite::class);
        $summary = $this->prophesize(Summary::class);

        $collection->getSuites()->willReturn([$suite->reveal()]);
        $suite->getUuid()->willReturn($uuid);
        $suite->getDate()->willReturn($date);
        $suite->getTag()->willReturn('foo');
        $suite->getEnvInformations()->willReturn([
            'vcs' => [
                'branch' => 'foo_branch',
            ],
        ]);
        $suite->getSummary()->willReturn($summary->reveal());
        $summary->getNbSubjects()->willReturn(5);
        $summary->getNbIterations()->willReturn(10);
        $summary->getNbRevolutions()->willReturn(1000);
        $summary->getMinTime()->willReturn(1);
        $summary->getMaxTime()->willReturn(2);
        $summary->getMeanTime()->willReturn(3);
        $summary->getMeanRelStDev()->willReturn(4);
        $summary->getTotalTime()->willReturn(5);

        return $collection;
    }
}
