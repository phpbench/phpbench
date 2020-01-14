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

namespace PhpBench\Tests\Unit\Storage\Archiver;

use PhpBench\Dom\Document;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Registry;
use PhpBench\Serializer\XmlDecoder;
use PhpBench\Serializer\XmlEncoder;
use PhpBench\Storage\Archiver\XmlArchiver;
use PhpBench\Storage\DriverInterface;
use PhpBench\Storage\HistoryEntry;
use PhpBench\Tests\Util\Workspace;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Filesystem;

class XmlArchiverTest extends TestCase
{
    private $registry;
    private $xmlEncoder;
    private $xmlDecoder;
    private $filesystem;
    private $archivePath;

    protected function setUp(): void
    {
        Workspace::initWorkspace();

        $this->archivePath = Workspace::getWorkspacePath();

        // create some files
        $dom = new Document();
        $dom->createRoot('hello');
        $dom->save($this->archivePath . '/' . '1.xml');
        $dom->save($this->archivePath . '/' . '2.txt');
        $dom->save($this->archivePath . '/' . '2.xml');

        $this->registry = $this->prophesize(Registry::class);
        $this->xmlEncoder = $this->prophesize(XmlEncoder::class);
        $this->xmlDecoder = $this->prophesize(XmlDecoder::class);
        $this->filesystem = $this->prophesize(Filesystem::class);

        $this->archiver = new XmlArchiver(
            $this->registry->reveal(),
            $this->xmlEncoder->reveal(),
            $this->xmlDecoder->reveal(),
            $this->archivePath,
            $this->filesystem->reveal()
        );

        $this->historyEntry = $this->prophesize(HistoryEntry::class);
        $this->output = new BufferedOutput();
        $this->storage = $this->prophesize(DriverInterface::class);
        $this->document = $this->prophesize(Document::class);
        $this->collection = $this->prophesize(SuiteCollection::class);
        $this->collection2 = $this->prophesize(SuiteCollection::class);

        $this->registry->getService()->willReturn($this->storage->reveal());
    }

    protected function tearDown(): void
    {
        Workspace::cleanWorkspace();
    }

    /**
     * It should create the archive directory if it doesn't exist.
     */
    public function testArchiveCreateDirectory()
    {
        $this->filesystem->exists($this->archivePath)->willReturn(false);
        $this->filesystem->mkdir($this->archivePath)->shouldBeCalled();
        $this->storage->history()->willReturn([]);
        $this->archiver->archive($this->output);
    }

    /**
     * It should not create a directory if it already exists.
     */
    public function testArchiveExistingDirectoryDoNotCreate()
    {
        $this->filesystem->exists($this->archivePath)->willReturn(true);
        $this->filesystem->mkdir($this->archivePath)->shouldNotBeCalled();
        $this->storage->history()->willReturn([]);
        $this->archiver->archive($this->output);
    }

    /**
     * It should skip existing archive history entries.
     * It should write non-existing archive entries.
     */
    public function testArchiveSkipExisting()
    {
        $this->filesystem->exists($this->archivePath)->willReturn(true);
        $this->storage->history()->willReturn([
            $this->createHistoryEntry(1),
            $this->createHistoryEntry(2),
            $this->createHistoryEntry(3),
        ]);
        $this->filesystem->exists($this->archivePath . '/1.xml')->willReturn(true);
        $this->filesystem->exists($this->archivePath . '/2.xml')->willReturn(false);
        $this->filesystem->exists($this->archivePath . '/3.xml')->willReturn(true);

        $this->storage->fetch(2)->willReturn($this->collection->reveal());
        $this->xmlEncoder->encode($this->collection->reveal())->willReturn($this->document->reveal());
        $this->document->save($this->archivePath . '/2.xml')->shouldBeCalled();

        $this->archiver->archive($this->output);
    }

    /**
     * It should restore to storage.
     * It should skip existing records.
     */
    public function testRestore()
    {
        $this->xmlDecoder->decode(Argument::type(Document::class))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->collection->reveal(), $this->collection2->reveal());

        $this->storage->has(1)->willReturn(true);
        $this->storage->has(2)->willReturn(false);
        $this->storage->store($this->collection->reveal())->shouldBeCalled();

        $this->archiver->restore($this->output);
    }

    private function createHistoryEntry($identifier)
    {
        return new HistoryEntry(
            $identifier,
            new \DateTime(),
            'foo',
            'branch',
            10,
            20,
            40,
            0.5,
            2,
            1.25,
            0.75,
            100
        );
    }

    private function createFileInfo($path)
    {
        $file = $this->prophesize(\SplFileInfo::class)->reveal();
        $file->isFile()->willReturn(true);
        $file->getExtension()->willReturn(substr($path, -3));
        $file->getFilename()->willReturn(basename($path));

        return $file;
    }
}
