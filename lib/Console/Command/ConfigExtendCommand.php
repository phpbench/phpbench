<?php

namespace PhpBench\Console\Command;

use PhpBench\Config\ConfigManipulator;
use PhpBench\Registry\ConfigurableRegistry;
use PhpBench\Registry\Registries;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ConfigExtendCommand extends Command
{
    public const ARG_REGISTRY = 'registry';
    public const ARG_PROTOTYPE = 'prototype';
    private const ARG_NAME = 'name';


    public function __construct(
        private ConfigManipulator $manipulator,
        private Registries $registries,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('config:extend');
        $this->setDescription('Generate a new service configuration baesd on a named prototype');
        $this->addArgument(self::ARG_REGISTRY, InputArgument::REQUIRED, 'Registry name, e.g. "generator"');
        $this->addArgument(self::ARG_PROTOTYPE, InputArgument::REQUIRED, 'Prototype service, e.g. "default"');
        $this->addArgument(self::ARG_NAME, InputArgument::REQUIRED, 'New service name, e.g. "acme"');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $registryName = $input->getArgument(self::ARG_REGISTRY);
        $serviceName = $input->getArgument(self::ARG_PROTOTYPE);
        $name = $input->getArgument(self::ARG_NAME);

        $registry = $this->registries->get($registryName);

        if (!$registry instanceof ConfigurableRegistry) {
            throw new RuntimeException('Service is not configurable');
        }

        $service = $registry->getConfig($serviceName);
        $this->manipulator->merge(
            $registry->getOptionName(),
            [
                $name => $service->getArrayCopy(),
            ],
        );
        $output->writeln(sprintf('<info>Updated:</> %s', $this->manipulator->configPath()));
        $output->writeln(sprintf(
            '<info>Set </>%s<info> named </>%s</info> <info>based on </>%s<info> at</> %s',
            $registryName,
            $name,
            $serviceName,
            $registry->getOptionName(),
        ));

        return 0;
    }
}
