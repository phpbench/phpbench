<?php

namespace PhpBench\Tests\Unit\Template\ObjectPathResolver;

use PHPUnit\Framework\TestCase;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\StringNode;
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
            $this->workspace()->path($path),
            $this->createResolver([
                'PhpBench\\Tests\\Unit' => $this->workspace()->path('templates')
            ])->resolvePath($this)
        );
    }

    public function testResolveParentClass(): void
    {
        $path = 'templates/IntegrationTestCase.phtml';
        $this->workspace()->put($path, '');

        self::assertEquals(
            $this->workspace()->path($path),
            $this->createResolver([
                'PhpBench\\Tests' => $this->workspace()->path('templates')
            ])->resolvePath($this)
        );
    }

    public function testResolveInterface(): void
    {
        $path = 'templates/Expression/Ast/Node.phtml';
        $this->workspace()->put($path, '');

        $object = new StringNode('hello');

        self::assertEquals(
            $this->workspace()->path($path),
            $this->createResolver([
                'PhpBench\\' => $this->workspace()->path('templates')
            ])->resolvePath($object)
        );
    }

    public function createResolver(array $prefixMap): ReflectionObjectPathResolver
    {
        return new ReflectionObjectPathResolver($prefixMap);
    }
}
