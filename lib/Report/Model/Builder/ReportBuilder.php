<?php

namespace PhpBench\Report\Model\Builder;

use PhpBench\Report\ComponentInterface;
use PhpBench\Report\Model\Report;

final class ReportBuilder
{
    /**
     * @var ComponentInterface[]
     */
    private array $objects = [];

    private ?string $description = null;

    private bool $tabbed = false;

    /**
     * @var string[] $tabLabels
     */
    private array $tabLabels = [];

    private function __construct(private readonly ?string $title = null)
    {
    }

    public static function create(string $title = null): self
    {
        return new self($title);
    }

    /**
     * @param string[] $labels
     */
    public function withTabLabels(array $labels): self
    {
        $this->tabLabels = $labels;

        return $this;
    }


    public function withDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function addObject(ComponentInterface $object): self
    {
        $this->objects[] = $object;

        return $this;
    }

    public function enableTabs(): self
    {
        $this->tabbed = true;

        return $this;
    }

    public function build(): Report
    {
        return new Report(
            $this->objects,
            $this->title,
            $this->tabbed,
            $this->description,
            $this->tabLabels
        );
    }
}
