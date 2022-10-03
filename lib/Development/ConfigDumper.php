<?php

namespace PhpBench\Development;

use PhpBench\DependencyInjection\ExtensionInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function json_encode;
use function mb_strlen;
use function method_exists;
use function str_repeat;

class ConfigDumper
{
    public const TITLE = 'Configuration';

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
        if (!method_exists(OptionsResolver::class, 'getInfo')) {
            return 'Config reference generation requires Symfony Options Resolver ^5.0';
        }

        $sections = [
            self::TITLE,
            $this->underline(self::TITLE, '='),
            '',
            '.. include:: configuration-prelude.rst',
            '',
            '.. contents::',
            '   :depth: 2',
            '   :local:',
            '',
        ];

        foreach ($this->extensions as $extensionClass) {
            $optionsResolver = new OptionsResolver();
            $extension = new $extensionClass();
            assert($extension instanceof ExtensionInterface);
            $extension->configure($optionsResolver);

            if (!$optionsResolver->getDefinedOptions()) {
                continue;
            }
            $inspector = new OptionsResolverIntrospector($optionsResolver);
            $sections[] = $this->generateSection($extensionClass, $optionsResolver, $inspector);
        }

        return implode("\n", $sections);
    }

    private function generateSection(string $extensionClass, OptionsResolver $optionsResolver, OptionsResolverIntrospector $inspector): string
    {
        $shortName = substr($extensionClass, (int)strrpos($extensionClass, '\\') + 1);
        $shortName = substr($shortName, 0, (int)strrpos($shortName, 'Extension'));
        $section = [
            $shortName,
            $this->underline($shortName, '-'),
            '',
            sprintf('Extension class: ``%s``', $extensionClass),
            ''
        ];

        foreach ($optionsResolver->getDefinedOptions() as $option) {
            $section[] = sprintf('.. _configuration_%s:', str_replace('.', '_', $option));
            $section[] = '';
            $section[] = $option;
            $section[] = $this->underline($option, '~');
            $section[] = '';
            $section[] = $optionsResolver->getInfo($option);
            $section[] = '';
            $section[] = sprintf('Default: ``%s``', $this->prettyPrint($inspector->getDefault($option)));
            $section[] = '';
            $section[] = sprintf('Types: ``%s``', $this->prettyPrint($inspector->getAllowedTypes($option)));
            $section[] = '';

            try {
                $section[] = sprintf('Allowed values: ``%s``', $this->prettyPrint($inspector->getAllowedValues($option)));
                $section[] = '';
            } catch (NoConfigurationException $e) {
            }
        }

        return implode("\n", $section);
    }

    private function underline(string $string, string $char): string
    {
        return str_repeat($char, mb_strlen($string));
    }

    private function prettyPrint($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value)) {
            return (string)$value;
        }

        if (is_null($value)) {
            return 'NULL';
        }

        return json_encode($value);
    }
}
