<?php

namespace PhpBench\Development;

use PhpBench\DependencyInjection\ExtensionInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function mb_strlen;
use function str_repeat;

class ConfigDumper
{
    const TITLE = 'Configuration';

    /**
     * @var class-string[]
     */
    private $extensions;

    /**
     * @param class-string[] $extensions
     */
    public function __construct(array $extensions)
    {
        $this->extensions = $extensions;
    }

    public function dump(): string
    {
        $sections = [
            self::TITLE,
            $this->underline(self::TITLE, '='),
            ''
        ];

        foreach ($this->extensions as $extensionClass) {
            $optionsResolver = new OptionsResolver();
            $extension = new $extensionClass;
            assert($extension instanceof ExtensionInterface);
            $extension->configure($optionsResolver);
            $inspector = new OptionsResolverIntrospector($optionsResolver);
            $sections[] = $this->generateSection($extensionClass, $optionsResolver, $inspector);
        }

        return implode("\n", $sections);
    }

    private function generateSection(string $extensionClass, OptionsResolver $optionsResolver, OptionsResolverIntrospector $inspector): string
    {
        $section = [
            $extensionClass,
            $this->underline($extensionClass, '-'),
            ''
        ];

        foreach ($optionsResolver->getDefinedOptions() as $option) {
            $section[] = sprintf('.. _config_%s:', str_replace('.', '_', $option));
            $section[] = '';
            $section[] = $option;
            $section[] = $this->underline($option, '~');
            $section[] = '';
            $section[] = sprintf('Default: ``%s``', json_encode($inspector->getDefault($option)));
            $section[] = $optionsResolver->getInfo($option);
        }

        return implode("\n", $section);
    }

    private function underline(string $string, string $char): string
    {
        return str_repeat($char, mb_strlen($string));
    }
}
