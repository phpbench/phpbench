<?php

namespace PhpBench\Environment\Provider;

use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Environment\ProviderInterface;
use PhpBench\Environment\Information;

abstract class AbstractRemoteProvider implements ProviderInterface
{
    /**
     * @var Launcher
     */
    private $launcher;

    public function __construct(Launcher $launcher)
    {
        $this->launcher = $launcher;
    }

    public function isApplicable()
    {
        return true;
    }

    public function getInformation()
    {
        return new Information(
            $this->name(),
            $this->getData()
        );
    }

    private function getData()
    {
        return $this->launcher->payload($this->template())->launch();
    }

    abstract protected function name(): string;

    abstract protected function template(): string;
}
