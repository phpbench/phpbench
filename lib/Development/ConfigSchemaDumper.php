<?php

namespace PhpBench\Development;

use PhpBench\PhpBench;
use function json_encode;
use function mb_strlen;
use function method_exists;
use PhpBench\DependencyInjection\ExtensionInterface;
use function str_repeat;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigSchemaDumper
{
    private const SCHEMA_URL = 'https://github.com/phpbench/phpbench/releases/download/%s/phpbench.schena.json';

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
          '$id' => sprintf(
              self::SCHEMA_URL,
              PhpBench::version()
          ),
          'title' => 'PHPBench configuration',
          'type' => 'object',
          'properties' => [
          ],
        ];
        foreach ($this->extensions as $extensionClass) {
            $optionsResolver = new OptionsResolver();
            $extension = new $extensionClass;
            assert($extension instanceof ExtensionInterface);
            $extension->configure($optionsResolver);

            if (!$optionsResolver->getDefinedOptions()) {
                continue;
            }
            $inspector = new OptionsResolverIntrospector($optionsResolver);
            foreach ($optionsResolver->getDefinedOptions() as $option) {
                $schema['properties'][$option] = [
                    'description' => $optionsResolver->getInfo($option),
                    'type' => $this->mapTypes($inspector->getAllowedTypes($option)),
                ];
            }
        }

        return (string)json_encode($schema);
    }

    /**
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
