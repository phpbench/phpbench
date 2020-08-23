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
use PhpBench\Serializer\XmlEncoder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DumpHandler
{
    public const OPT_DUMP_FILE = 'dump-file';
    public const OPT_DUMP = 'dump';

    /**
     * @var XmlEncoder
     */
    private $xmlEncoder;

    public function __construct(XmlEncoder $xmlEncoder)
    {
        $this->xmlEncoder = $xmlEncoder;
    }

    public static function configure(Command $command)
    {
        $command->addOption(self::OPT_DUMP_FILE, 'd', InputOption::VALUE_OPTIONAL, 'Dump XML result to named file');
        $command->addOption(self::OPT_DUMP, null, InputOption::VALUE_NONE, 'Dump XML result to stdout and suppress all other output');
    }

    public function dumpFromInput(InputInterface $input, OutputInterface $output, SuiteCollection $collection)
    {
        if (false === $input->getOption(self::OPT_DUMP_FILE) && false === $input->getOption(self::OPT_DUMP)) {
            return;
        }

        $dom = $this->xmlEncoder->encode($collection);

        if ($dumpFile = $input->getOption(self::OPT_DUMP_FILE)) {
            $xml = (string)$dom->dump();
            file_put_contents($dumpFile, $xml);
            $output->writeln('Dumped result to ' . $dumpFile);
        }

        if ($input->getOption(self::OPT_DUMP)) {
            $xml = $dom->dump();
            $output->write($xml);
        }
    }
}
