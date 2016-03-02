<?php

namespace PhpBench\Storage\Driver\Xml;

use PhpBench\Storage\DriverInterface;
use PhpBench\Serializer\XmlEncoder;
use Symfony\Component\Filesystem\Filesystem;
use PhpBench\Model\SuiteCollection;

class Persister
{
    private $xmlEncoder;
    private $path;
    private $filesystem;

    public function __construct(XmlEncoder $xmlEncoder, $path, Filesystem $filesystem = null)
    {
        $this->xmlEncoder = $xmlEncoder;
        $this->path = $path;
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    /**
     * {@inheritdoc}
     */
    public function persist(SuiteCollection $collection)
    {
        foreach ($collection as $suite) {
            $filename = XmlDriverUtil::getFilenameForSuite($suite);

            $collection = new SuiteCollection([$suite]);
            $document = $this->xmlEncoder->encode($collection);
            $path = sprintf('%s/%s', $this->path, $filename);
            if (!$this->filesystem->exists(dirname($path))) {
                $this->filesystem->mkdir(dirname($path));
            }

            $document->save($path);
        }
    }
}

