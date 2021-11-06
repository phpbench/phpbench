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
use PhpBench\Serializer\XmlDecoder;
use PhpBench\Storage\StorageRegistry;
use PhpBench\Storage\UuidResolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class SuiteCollectionHandler
{
    /**
     * @var XmlDecoder
     */
    private $xmlDecoder;

    /**
     * @var StorageRegistry
     */
    private $storage;

    /**
     * @var UuidResolver
     */
    private $refResolver;

    public function __construct(
        XmlDecoder $xmlDecoder,
        StorageRegistry $storage,
        UuidResolver $refResolver
    ) {
        $this->xmlDecoder = $xmlDecoder;
        $this->storage = $storage;
        $this->refResolver = $refResolver;
    }

    public static function configure(Command $command): void
    {
        $command->addOption('ref', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Reference to an existing run - can be a UUID or tag or special word (e.g. latest)');
        $command->addOption('file', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Report XML file');
    }

    public function suiteCollectionFromInput(InputInterface $input): SuiteCollection
    {
        $files = $input->getOption('file');
        $refs = $input->getOption('ref');
        assert(is_array($files));
        assert(is_array($refs));

        $subjectPatterns = $input->hasOption('filter') ? $input->getOption('filter') : [];
        $variantPatterns = $input->hasOption('variant') ? $input->getOption('variant') : [];

        if (!$files && !$refs) {
            throw new \InvalidArgumentException(
                'You must specify at least one of `--file` and/or `--ref`'
            );
        }

        $collection = new SuiteCollection();

        if ($files) {
            $collection->mergeCollection(
                $this->xmlDecoder->decodeFiles($files)
            );
        }

        if ($refs) {
            foreach ($refs as $ref) {
                $collection->mergeCollection($this->storage->getService()->fetch(
                    $this->refResolver->resolve($ref)
                )->filter($subjectPatterns, $variantPatterns));
            }
        }

        return $collection;
    }
}
