<?php

namespace PhpBench\Extensions\Elastic\Command;

use Symfony\Component\Console\Command\Command;
use PhpBench\Extensions\Elastic\Driver\ElasticClient;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class InstallCommand extends Command
{
    /**
     * @var ElasticClient
     */
    private $client;

    public function __construct(ElasticClient $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    protected function configure()
    {
        $this->setName('elastic:install');
        $this->addOption('purge', null, InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mapping = json_decode(file_get_contents(__DIR__ . '/../mapping.json'), true);

        if (true === $input->getOption('purge')) {
            $this->client->purge();
        }

        $this->client->install($mapping);
        $output->writeln('Done');
    }
}
