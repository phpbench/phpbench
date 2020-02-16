<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Registry;

use PhpBench\DependencyInjection\Container;
use PhpBench\Json\JsonDecoder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Registry that adds configuration capabilities to the service
 * registry class.
 *
 * TODO: Coupling configuration to the registry is convenient but the two are
 *       effectively unrelated and could be decoupled.
 *
 * ```
 * $config = $reg->getConfig('foobar');
 * ```
 */
class ConfigurableRegistry extends Registry
{
    private $configs = [];
    private $jsonDecoder;

    /**
     * @var array
     */
    private $resolvedConfigs;

    public function __construct(
        $serviceType,
        Container $container,
        JsonDecoder $jsonDecoder
    ) {
        parent::__construct($serviceType, $container);
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * Return the named configuration.
     *
     * @return Config
     */
    public function getConfig($name)
    {
        if (is_array($name)) {
            $config = $name;
            $name = uniqid();
            $this->setConfig($name, $config);
        }

        $name = trim($name);
        $name = $this->processRawCliConfig($name);

        if (!isset($this->configs[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'No %s configuration named "%s" exists. Known configurations: "%s"',
                $this->serviceType,
                $name,
                implode('", "', array_keys($this->configs))
            ));
        }

        if (!isset($this->resolvedConfigs[$name])) {
            $this->resolveConfig($name);
        }

        return $this->resolvedConfigs[$name];
    }

    /**
     * Set a named configuration.
     *
     * Note that all configurations must be associated with a named service
     * via a configuration key equal to the configuration service type of this registry.
     *
     * @param string $name
     * @param array $config
     */
    public function setConfig($name, array $config)
    {
        if (isset($this->configs[$name])) {
            throw new \InvalidArgumentException(sprintf(
                '%s config "%s" already exists.',
                $this->serviceType,
                $name
            ));
        }

        $this->configs[$name] = $config;
    }

    /**
     * Recursively merge configs (having the "extends" key) which extend
     * another report.
     *
     * @param string $name
     *
     * @return void
     */
    private function resolveConfig($name)
    {
        $config = $this->configs[$name];

        if (isset($config['extends'])) {
            $extended = $this->getConfig($config['extends']);

            if (isset($config[$this->serviceType]) && ($extended[$this->serviceType] != $config[$this->serviceType])) {
                throw new \InvalidArgumentException(sprintf(
                    '%s configuration for service "%s" cannot extend configuration for different service "%s"',
                    $this->serviceType,
                    $config[$this->serviceType],
                    $extended[$this->serviceType]
                ));
            }

            unset($config['extends']);
            $config = array_merge(
                $extended->getArrayCopy(),
                $config
            );
        }

        if (!isset($config[$this->serviceType])) {
            throw new \InvalidArgumentException(sprintf(
                '%s configuration must EITHER indicate its target %s service with the "%s" key or extend an existing configuration with the "extends" key.',
                $this->serviceType,
                $this->serviceType,
                $this->serviceType
            ));
        }

        /** @var RegistrableInterface $service */
        $service = $this->getService($config[$this->serviceType]);

        $options = new OptionsResolver();
        $options->setRequired([$this->serviceType]);
        $service->configure($options);
        $config = $options->resolve($config);

        $this->resolvedConfigs[$name] = new Config($name, $config);
    }

    /**
     * Process raw configuration as received from the CLI, for example:.
     *
     * ````
     * {"generator": "table", "sort": ["time"]}
     * ````
     *
     * Or simply the name of a pre-configured configuration to use:
     *
     * ````
     * table
     * ````
     *
     * @param string $rawConfig
     *
     * @return string
     */
    private function processRawCliConfig($rawConfig)
    {
        if (preg_match(Config::NAME_REGEX, $rawConfig)) {
            return $rawConfig;
        }

        $config = $this->jsonDecoder->decode($rawConfig);
        $configName = uniqid();
        $this->setConfig($configName, $config);

        return $configName;
    }
}
