<?php

namespace PhpBench\Development;

use Generator;
use PhpBench\DependencyInjection\Container;
use PhpBench\Registry\RegistrableInterface;
use PhpBench\Registry\Registry;
use RuntimeException;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function json_encode;
use function method_exists;

class OptionDumper
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var array<string,string>
     */
    private $typeToRegistryMap;

    /**
     * @param array<string,string> $nameToRegistryMap
     */
    public function __construct(Container $container, array $nameToRegistryMap)
    {
        $this->container = $container;
        $this->typeToRegistryMap = $nameToRegistryMap;
    }

    /**
     * @return Generator<string,string>
     */
    public function dump(string $type): Generator
    {
        if (!method_exists(OptionsResolver::class, 'getInfo')) {
            return 'Config reference generation requires Symfony Options Resolver ^5.0';
        }

        if (!isset($this->typeToRegistryMap[$type])) {
            throw new RuntimeException(sprintf(
                'Do not know about registry of type "%s", known registries: "%s"',
                $type,
                implode('", "', array_keys($this->typeToRegistryMap))
            ));
        }

        yield from $this->dumpOptions(
            $this->container->get($this->typeToRegistryMap[$type]),
            $type
        );
    }

    /**
     * @param Registry<RegistrableInterface> $registry
     *
     * @return Generator<string, string>
     */
    private function dumpOptions(Registry $registry, string $type): Generator
    {
        foreach ($registry->getServiceNames() as $serviceName) {
            $service = $registry->getService($serviceName);

            if (!$service instanceof RegistrableInterface) {
                continue;
            }

            $optionsResolver = new OptionsResolver();
            $service->configure($optionsResolver);

            if (!$optionsResolver->getDefinedOptions()) {
                continue;
            }
            $inspector = new OptionsResolverIntrospector($optionsResolver);

            try {
                yield $serviceName => $this->generateSection($optionsResolver, $inspector, $type, $serviceName);
            } catch (NoConfigurationException $noConfig) {
                throw new RuntimeException(sprintf(
                    'Could not generate doc for "%s": %s',
                    get_class($service),
                    $noConfig->getMessage()
                ));
            }
        }
    }

    private function generateSection(
        OptionsResolver $optionsResolver,
        OptionsResolverIntrospector $inspector,
        string $type,
        string $serviceName
    ): string {
        $section = [];

        foreach ($optionsResolver->getDefinedOptions() as $option) {
            $description = $optionsResolver->getInfo($option);
            $name = $option;
            $default = 'n/a';

            try {
                $default = $this->prettyPrint($inspector->getDefault($option));
            } catch (NoConfigurationException $no) {
            }
            $types = $this->prettyPrint($inspector->getAllowedTypes($option));

            $section[] = '';
            $section[] = sprintf('.. _%s_%s_option_%s:', $type, $serviceName, $option);
            $section[] = '';
            $section[] = sprintf('**%s**:', $name);
            $section[] = sprintf('  Type(s): ``%s``, Default: ``%s``', $types, $default);

            if ($description) {
                $section[] = '';
                $section[] = sprintf('  %s', $description);
            }
        }

        return implode("\n", $section);
    }

    /**
     * @param mixed $value
     */
    private function prettyPrint($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            if (count($value) === 1) {
                return implode(', ', array_map(function ($value) {
                    return $this->prettyPrint($value);
                }, $value));
            }

            return sprintf('[%s]', implode(', ', array_map(function ($value) {
                return $this->prettyPrint($value);
            }, $value)));
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
