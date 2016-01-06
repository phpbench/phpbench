<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Registry;

use JsonSchema\Validator;
use PhpBench\DependencyInjection\Container;

/**
 * Service and configuration registry.
 *
 * Lazily instantiates tagged services which are associated with a name
 * and stores configurations which are relevant to these services.
 *
 * Services using this registry will typically used in association with a configuration, e.g.
 *
 * ```
 * $config = $reg->getConfig('foobar');
 * $reg->getService($config['renderer']);
 * $reg->render($something, $config);
 * ```
 */
class Registry
{
    private $serviceMap = array();
    private $container;
    private $configs = array();
    private $services = array();
    private $validator;
    private $serviceType;

    public function __construct($serviceType, Container $container, Validator $validator)
    {
        $this->serviceType = $serviceType;
        $this->container = $container;
        $this->validator = $validator ?: new Validator();
    }

    /**
     * Register a service ID with against the given name.
     *
     * @param string $name
     * @param string $serviceId
     */
    public function registerService($name, $serviceId)
    {
        $this->serviceMap[$name] = $serviceId;
        $this->services[$name] = null;
    }

    /**
     * Directly set a named service.
     *
     * @param string $name
     * @param object $object
     */
    public function setService($name, $object)
    {
        if (isset($this->services[$name])) {
            throw new \InvalidArgumentException(sprintf(
                '%s service "%s" already exists.',
                $this->serviceType,
                $name
            ));
        }

        $this->services[$name] = $object;
    }

    /**
     * Return the named service, lazily creating it from the container
     * if it has not yet been accessed.
     *
     * @param string $name
     *
     * @return object
     */
    public function getService($name)
    {
        if (isset($this->services[$name])) {
            return $this->services[$name];
        }

        $this->assertServiceExists($name);
        $this->services[$name] = $this->container->get($this->serviceMap[$name]);

        return $this->services[$name];
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

        $name = $this->processRawCliConfig($name);

        if (!isset($this->configs[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'No %s configuration named "%s" exists. Known configurations: "%s"',
                $this->serviceType,
                $name,
                implode('", "', array_keys($this->configs))
            ));
        }

        return $this->configs[$name];
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

        $config = $this->resolveConfig($config);

        if (!isset($config[$this->serviceType])) {
            throw new \InvalidArgumentException(sprintf(
                '%s configuration must indicate its target %s service with the "%s" key.',
                $this->serviceType,
                $this->serviceType,
                $this->serviceType
            ));
        }

        $service = $this->getService($config[$this->serviceType]);

        // eagerly validate
        $config = $this->mergeAndValidateConfig($service, $config);
        $this->configs[$name] = new Config($config);
    }

    /**
     * Recursively merge configs (having the "extends" key) which extend
     * another report.
     *
     * @param array $config
     * @param string $getMethod
     *
     * @return array
     */
    private function resolveConfig(array $config)
    {
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
            $config = array_replace_recursive(
                $this->resolveConfig($extended->getArrayCopy()),
                $config
            );
        }

        return $config;
    }

    /**
     * Merge the given config on to "configurable" (either a GeneratorInterface
     * or a RendererInterface) instance's default config and validate it
     * according to the "configurable" instance's JSON schema.
     *
     * @param ConfigurableInterface $configurable
     * @param array $config
     *
     * @return array
     */
    private function mergeAndValidateConfig(RegistrableInterface $configurable, array $config)
    {
        $config = array_replace_recursive($configurable->getDefaultConfig(), $config);

        // not sure if there is a better way to convert the schema array to objects
        // as expected by the validator.
        $validationConfig = json_decode(json_encode($config));

        $schema = $configurable->getSchema();

        if (!is_array($schema)) {
            throw new \InvalidArgumentException(sprintf(
                'Configurable class "%s" must return the JSON schema as an array',
                get_class($configurable)
            ));
        }

        $schema['properties'][$this->serviceType] = array('type' => 'string');

        // convert the schema to a \stdClass
        $schema = json_decode(json_encode($schema));

        // json_encode encodes an array instead of an object if the schema
        // is empty. JSON schema requires an object.
        if (empty($schema)) {
            $schema = new \stdClass();
        }

        $this->validator->check($validationConfig, $schema);

        if (!$this->validator->isValid()) {
            $errorString = array();
            foreach ($this->validator->getErrors() as $error) {
                $errorString[] = sprintf('[%s] %s', $error['property'], $error['message']);
            }

            throw new \InvalidArgumentException(sprintf(
                'Invalid JSON: %s%s',
                PHP_EOL . PHP_EOL . PHP_EOL, implode(PHP_EOL, $errorString)
            ));
        }

        return $config;
    }

    private function assertServiceExists($name)
    {
        if (!array_key_exists($name, $this->services)) {
            throw new \InvalidArgumentException(sprintf(
                '%s service "%s" does not exist. Registered %s services: "%s"',
                $this->serviceType,
                $name,
                $this->serviceType,
                implode('", "', array_keys($this->services))
            ));
        }
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
     * @param array $rawConfigs
     *
     * @return array
     */
    private function processRawCliConfig($rawConfig)
    {
        // If it doesn't look like a JSON string, assume it is the name of a config
        if (substr($rawConfig, 0, 1) !== '{') {
            return $rawConfig;
        }

        $config = json_decode($rawConfig, true);

        if (null === $config) {
            throw new \InvalidArgumentException(sprintf(
                'Could not decode JSON string: %s', $rawConfig
            ));
        }

        $configName = uniqid();
        $this->setConfig($configName, $config);

        return $configName;
    }
}
