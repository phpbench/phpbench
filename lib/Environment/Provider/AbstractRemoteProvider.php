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

namespace PhpBench\Environment\Provider;

use PhpBench\Environment\Information;
use PhpBench\Environment\ProviderInterface;
use PhpBench\Remote\Launcher;

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

    public function isApplicable(): bool
    {
        return true;
    }

    public function getInformation(): Information
    {
        return new Information(
            $this->name(),
            $this->getData()
        );
    }

    private function getData(): array
    {
        return $this->launcher->payload($this->template())->launch();
    }

    abstract protected function name(): string;

    abstract protected function template(): string;
}
