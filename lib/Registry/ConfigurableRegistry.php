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
use PhpBench\Json\JsonDecoder;

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
    private $validator;
    private $jsonDecoder;

    public function __construct(
        $serviceType,
        Container $container,
        Validator $validator,
        JsonDecoder $jsonDecoder
    ) {
        parent::__construct($serviceType, $container);
        $this->validator = $validator ?: new Validator();
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
                '%s configuration must EITHER indicate its target %s service with the "%s" key or extend an existing configuration with the "extends" key.',
                $this->serviceType,
                $this->serviceType,
                $this->serviceType
            ));
        }

        $service = $this->getService($config[$this->serviceType]);

        // eagerly validate
        $config = $this->mergeAndValidateConfig($service, $config);
        $this->configs[$name] = new Config($name, $config);
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
            $config = array_merge(
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
        $config = array_merge($configurable->getDefaultConfig(), $config);

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

        $schema['properties'][$this->serviceType] = ['type' => 'string'];
        $schema['properties']['_name'] = ['type' => 'string'];

        // convert the schema to a \stdClass
        $schema = json_decode(json_encode($schema));

        // json_encode encodes an array instead of an object if the schema
        // is empty. JSON schema requires an object.
        if (empty($schema)) {
            $schema = new \stdClass();
        }

        $this->validator->check($validationConfig, $schema);

        if (!$this->validator->isValid()) {
            $errorString = [];
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
        if (preg_match(Config::NAME_REGEX, $rawConfig)) {
            return $rawConfig;
        }

        $config = $this->jsonDecoder->decode($rawConfig);
        $configName = uniqid();
        $this->setConfig($configName, $config);

        return $configName;
    }
}
