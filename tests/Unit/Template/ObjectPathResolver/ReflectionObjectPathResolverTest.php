<?php

namespace PhpBench\Tests\Unit\Template\ObjectPathResolver;

use PhpBench\Template\ObjectPathResolver\ReflectionObjectPathResolver;
use PhpBench\Tests\IntegrationTestCase;

class ReflectionObjectPathResolverTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function testResolvePrimaryPath(): void
    {
        $path = 'templates/Template/ObjectPathResolver/ReflectionObjectPathResolverTest.phtml';
        $this->workspace()->put($path, '');

        self::assertEquals(
            [$this->workspace()->path($path)],
            $this->createResolver([
                'PhpBench\\Tests\\Unit' => $this->workspace()->path('templates')
            ])->resolvePaths($this)
        );
    }

    public function testResolveParentClass(): void
    {
        $path = 'templates/IntegrationTestCase.phtml';
        $this->workspace()->put($path, '');

        self::assertEquals(
            [
                $this->workspace()->path('templates/Unit/Template/ObjectPathResolver/ReflectionObjectPathResolverTest.phtml'),
                $this->workspace()->path($path),
                $this->workspace()->path('templates/TestCase.phtml')
            ],
            $this->createResolver([
                'PhpBench\\Tests' => $this->workspace()->path('templates')
            ])->resolvePaths($this)
        );
    }

    public function createResolver(array $prefixMap): ReflectionObjectPathResolver
    {
        return new ReflectionObjectPathResolver($prefixMap);
    }
}
