<?php

namespace PhpBench\Tests\Unit\Template;

use PhpBench\Template\ObjectPathResolver\MappedObjectPathResolver;
use PhpBench\Template\ObjectRenderer;
use PhpBench\Template\TemplateService\MappedTemplateService;
use PhpBench\Tests\IntegrationTestCase;

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

    public function testExposesServices(): void
    {
        $this->workspace()->put('foo.html', 'Hello <?php echo $this->foobar->hello() ?>');
        self::assertEquals('Hello Hello', $this->createRenderer([
            'foobar' => new class () {
                public function hello()
                {
                    return 'Hello';
                }
            }
        ])->render(new Foobar()));
    }

    public function testThrowsExceptionWhenServiceNotFound(): void
    {
        $this->expectExceptionMessage('Unknown template service "barfoo", known template services: ""');
        $this->workspace()->put('foo.html', 'Hello <?php echo $this->barfoo->barfoo() ?>');
        $this->createRenderer([])->render(new Foobar());
    }

    public function createRenderer(array $services = []): ObjectRenderer
    {
        return new ObjectRenderer(
            new MappedObjectPathResolver([
                Foobar::class => 'foo.html',
                Barfoo::class => 'bar.html'
            ]),
            [
                $this->workspace()->path()
            ],
            new MappedTemplateService($services)
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
