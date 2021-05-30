<?php

namespace PhpBench\Report;

use Psr\Container\ContainerInterface;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ComponentGeneratorAgent
{
    /**
     * @var array<int|string,string>
     */
    private $componentMap;

    /**
     * @var array<string, OptionsResolver>
     */
    private $resolvers = [];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param array<int|string,string> $componentMap
     */
    public function __construct(ContainerInterface $container, array $componentMap)
    {
        $this->componentMap = $componentMap;
        $this->container = $container;
    }

    public function get(string $name): ComponentGeneratorInterface
    {
        if (!isset($this->componentMap[$name])) {
            throw new RuntimeException(sprintf(
                'Component "%s" not known, known components: "%s"',
                $name, implode('", "', array_keys($this->componentMap))
            ));
        }

        return $this->container->get($this->componentMap[$name]);
    }

    /**
     * @param parameters $config
     *
     * @return parameters
     */
    public function resolveConfig(ComponentGeneratorInterface $generator, array $config): array
    {
        return $this->resolveResolver($generator)->resolve($config);
    }

    private function resolveResolver(ComponentGeneratorInterface $generator): OptionsResolver
    {
        $cacheKey = get_class($generator);

        if (isset($this->resolvers[$cacheKey])) {
            return $this->resolvers[$cacheKey];
        }
        $resolver = new OptionsResolver();
        $generator->configure($resolver);
        $this->resolvers[$cacheKey] = $resolver;

        return $resolver;
    }
}
