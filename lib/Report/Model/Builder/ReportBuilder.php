<?php

namespace PhpBench\Report\Model\Builder;

use PhpBench\Report\ComponentInterface;
use PhpBench\Report\Model\Report;

final class ReportBuilder
{
    /**
     * @var string|null
     */
    private $title;

    /**
     * @var ComponentInterface[]
     */
    private $objects = [];

    /**
     * @var string|null
     */
    private $description;

    private function __construct(string $title = null)
    {
        $this->title = $title;
    }

    public static function create(string $title = null): self
    {
        return new self($title);
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

    public function build(): Report
    {
        return new Report($this->objects, $this->title, $this->description);
    }
}
