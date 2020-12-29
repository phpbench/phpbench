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

namespace PhpBench\Console\Command\Handler;

use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Registry;
use PhpBench\Serializer\XmlDecoder;
use PhpBench\Storage\RefResolverInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class SuiteCollectionHandler
{
    private $xmlDecoder;
    private $storage;

    /**
     * @var RefResolverInterface
     */
    private $refResolver;

    public function __construct(
        XmlDecoder $xmlDecoder,
        Registry $storage,
        RefResolverInterface $refResolver
    ) {
        $this->xmlDecoder = $xmlDecoder;
        $this->storage = $storage;
        $this->refResolver = $refResolver;
    }

    public static function configure(Command $command): void
    {
        $command->addOption('uuid', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Run UUID');
        $command->addOption('file', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Report XML file');
    }

    public function suiteCollectionFromInput(InputInterface $input): SuiteCollection
    {
        $files = $input->getOption('file');
        $uuids = $input->getOption('uuid');

        if (!$files && !$uuids) {
            throw new \InvalidArgumentException(
                'You must specify at least one of `--file` and/or `--uuid`'
            );
        }

        $collection = new SuiteCollection();

        if ($files) {
            $collection->mergeCollection(
                $this->xmlDecoder->decodeFiles($files)
            );
        }

        if ($uuids) {
            foreach ($uuids as $uuid) {
                $uuid = $this->refResolver->resolve($uuid);
                $collection->mergeCollection(
                    $this->storage->getService()->fetch($uuid)
                );
            }
        }

        return $collection;
    }
}
