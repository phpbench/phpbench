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
    public function __construct(private readonly Launcher $launcher)
    {
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

    /**
     * @return array<string, mixed>
     */
    private function getData(): array
    {
        return $this->launcher->payload($this->template())->launch();
    }

    abstract protected function name(): string;

    abstract protected function template(): string;
}
