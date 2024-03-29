<?php

namespace PhpBench\Color;

use RuntimeException;

/** final */class Color
{
    final private function __construct(private readonly int $red, private readonly int $green, private readonly int $blue)
    {
    }

    public static function fromHex(string $hex): self
    {
        $hexRgb = sscanf(ltrim($hex, '#'), '%02s%02s%02s');

        if (!is_array($hexRgb)) {
            throw new RuntimeException(sprintf(
                'Could not parse hex color "%s"',
                $hex
            ));
        }

        return new static(...array_map(function (string $hex): int {
            return (int)hexdec($hex);
        }, $hexRgb));
    }

    /**
     * @return array{int,int,int}
     */
    public function toTuple(): array
    {
        return [
            $this->red,
            $this->green,
            $this->blue
        ];
    }

    public function toHex(): string
    {
        return implode('', array_map(function (int $number) {
            return sprintf('%02s', dechex($number));
        }, $this->toTuple()));
    }

    public static function fromRgb(int $red, int $green, int $blue): self
    {
        return new static($red, $green, $blue);
    }
}
