<?php

namespace PhpBench\Color;

final class GradientBuilder
{
    /**
     * @var array<int, array{string, int}>
     */
    private $series = [];

    /**
     * @var array<string, Gradient>
     */
    private array $cache = [];

    public function __construct(private string $startColor = '#000000')
    {
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
