<?php

namespace PhpBench\Extension;

use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Template\ObjectPathResolver\ReflectionObjectPathResolver;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemplateExtension implements ExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(Container $container): void
    {
        $container->register(ReflectionObjectPathResolver::class, function (Container $container) {
            return new ReflectionObjectPathResolver([
                'PhpBench\\' => __DIR__ . '/../../templates'
            ]);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $resolver): void
    {
    }
}
