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
    private $xmlEncoder;

    public function __construct(XmlEncoder $xmlEncoder)
    {
        $this->xmlEncoder = $xmlEncoder;
    }

    public static function configure(Command $command)
    {
        $command->addOption('dump-file', 'd', InputOption::VALUE_OPTIONAL, 'Dump XML result to named file');
        $command->addOption('dump', null, InputOption::VALUE_NONE, 'Dump XML result to stdout and suppress all other output');
    }

    public function dumpFromInput(InputInterface $input, OutputInterface $output, SuiteCollection $collection)
    {
        if (false === $input->getOption('dump-file') && false === $input->getOption('dump')) {
            return;
        }

        $dom = $this->xmlEncoder->encode($collection);

        if ($dumpFile = $input->getOption('dump-file')) {
            $xml = $dom->dump();
            file_put_contents($dumpFile, $xml);
            $output->writeln('Dumped result to ' . $dumpFile);
        }

        if ($input->getOption('dump')) {
            $xml = $dom->dump();
            $output->write($xml);
        }
    }
}
