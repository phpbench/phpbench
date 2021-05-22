<?php

namespace PhpBench\Color;

final class GradientBuilder
{
    /**
     * @var string
     */
    private $startColor;

    /**
     * @var array<int, array{string, int}>
     */
    private $series = [];

    private $cache = [];

    public function __construct(string $startColor = '#000000')
    {
        $this->startColor = $startColor;
    }

    public function create(string $startColor): self
    {
        $this->series = [];
        $this->startColor = $startColor;

        return $this;
    }

    public function to(string $color, int $steps): self
    {
        $this->series[] = [$color, $steps];

        return $this;
    }

    public function build(): Gradient
    {
        $hash = serialize($this->series).$this->startColor;

        if (isset($this->cache[$hash])) {
            return $this->cache[$hash];
        }

        $gradient = Gradient::start(Color::fromHex($this->startColor));

        foreach ($this->series as [$color, $steps]) {
            $gradient = $gradient->to(Color::fromHex($color), $steps);
        }

        $this->cache[$hash] = $gradient;

        return $gradient;
    }
}
