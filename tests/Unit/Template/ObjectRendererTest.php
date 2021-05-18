<?php

namespace PhpBench\Tests\Unit\Template;

use PHPUnit\Framework\TestCase;
use PhpBench\Template\ObjectRenderer;
use PhpBench\Tests\IntegrationTestCase;
use PhpBench\Tests\Unit\Template\ObjectPathResolver\MappedObjectPathResolver;

class ObjectRendererTest extends IntegrationTestCase
{
    public function testRender(): void
    {
        $this->workspace()->put('foo.html', 'Hello');
        self::assertEquals('Hello', $this->createRenderer()->render(new Foobar()));
    }

    public function testRenderTemplateWhichRendersTemplate(): void
    {
        $this->workspace()->put('foo.html', 'Hello <?php echo $this->render($object->barfoo()) ?>');
        $this->workspace()->put('bar.html', 'World');
        self::assertEquals('Hello World', $this->createRenderer()->render(new Foobar()));
    }


    public function createRenderer(): ObjectRenderer
    {
        return new ObjectRenderer(
            new MappedObjectPathResolver([
                Foobar::class => $this->workspace()->path('foo.html'),
                Barfoo::class => $this->workspace()->path('bar.html')
            ])
        );
    }
}

class Foobar
{
    public function barfoo(): Barfoo
    {
        return new Barfoo();
    }
}

class Barfoo
{
}
