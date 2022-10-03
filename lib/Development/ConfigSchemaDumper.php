<?php

namespace PhpBench\Development;

use PhpBench\DependencyInjection\ExtensionInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function json_encode;
use function method_exists;

class ConfigSchemaDumper
{
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

        $schema = [
          '$schema' => 'https =>//json-schema.org/draft/2020-12/schema',
          'title' => 'PHPBench configuration',
          'type' => 'object',
          'properties' => [
              '$include' => [
                  'description' => 'Include another config file relative to this one',
                  'type' => ['string', 'array'],
              ],
              '$include-glob' => [
                  'description' => 'Include config files using a glob pattern. Paths are relative to the config file',
                  'type' => ['string', 'array'],
              ],
          ],
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

            foreach ($optionsResolver->getDefinedOptions() as $option) {
                $meta = [
                    'description' => $optionsResolver->getInfo($option),
                    'type' => $this->mapTypes($inspector->getAllowedTypes($option)),
                ];

                try {
                    $values = $inspector->getAllowedValues($option);
                    $meta['enum'] = $values;
                } catch (NoConfigurationException $e) {
                }
                $schema['properties'][$option] = $meta;
            }
        }

        return (string)json_encode($schema, JSON_PRETTY_PRINT);
    }

    /**
     * @param string[] $types
     *
     * @return string[]
     */
    private function mapTypes(array $types): array
    {
        return array_map(function (string $type) {
            if ($type === 'array') {
                return 'object';
            }

            if ($type === 'bool') {
                return 'boolean';
            }

            if ($type === 'int') {
                return 'integer';
            }

            if ($type === 'float') {
                return 'number';
            }

            if (substr($type, -2) === '[]') {
                return 'array';
            }

            return $type;
        }, $types);
    }
}
