<?php

namespace PhpBench\Tests\Unit\Expression\Theme;

use PhpBench\Expression\Ast\PercentDifferenceNode;
use PhpBench\Expression\Theme\SolarizedTheme;
use PHPUnit\Framework\TestCase;

use const INF;

final class SolarizedThemeTest extends TestCase
{
    public function testInfiniteIncrease(): void
    {
        $theme = new SolarizedTheme();
        $color = $theme->colors()[PercentDifferenceNode::class](new PercentDifferenceNode(INF));

        self::assertEquals('fg=#ff0000', $color);
    }
}
