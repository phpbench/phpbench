<?php

namespace PhpBench\Config;

use RuntimeException;
use stdClass;

final class ConfigManipulator
{
    public function __construct(private ?string $schemaPath, private string $configPath)
    {
    }

    public function configPath(): string
    {
        return $this->configPath;
    }

    public function initialize(): void
    {
        $json = $this->openConfig();

        if (null !== $this->schemaPath) {
            $json->{'$schema'} = $this->schemaPath;
        }
        $this->writeConfig($json);
    }

    /**
     * @param mixed $value
     */
    public function set(string $key, $value): void
    {
        $json = $this->openConfig();
        $json->{$key} = $value;
        $this->writeConfig($json);
    }

    public function delete(string $key): void
    {
        $json = $this->openConfig();
        unset($json->{$key});
        $this->writeConfig($json);
    }

    private function createConfig(): string
    {
        $value = [ '$schema' => $this->schemaPath ];

        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    private function openConfig(): stdClass
    {
        if (!file_exists($this->configPath)) {
            if (false === file_put_contents($this->configPath, $this->createConfig())) {
                throw new RuntimeException(sprintf(
                    'Could not write config file to "%s"',
                    $this->configPath
                ));
            }
        }

        $config = file_get_contents($this->configPath);

        if (false === $config) {
            throw new RuntimeException(sprintf(
                'Could not read config file "%s"',
                $this->configPath
            ));
        }

        $json = json_decode($config);

        if (!$json instanceof stdClass) {
            throw new RuntimeException(sprintf(
                'Could not decode JSON file "%s"',
                $this->configPath
            ));
        }

        return $json;
    }

    /**
     * @param mixed $value
     */
    private function writeConfig($value): void
    {
        file_put_contents($this->configPath, json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * @param array<mixed> $value
     */
    public function merge(string $key, array $value): void
    {
        $config = $this->openConfig();

        if (!isset($config->{$key})) {
            $config->{$key} = new stdClass();
        }

        if (!is_object($config->{$key})) {
            throw new RuntimeException(sprintf(
                'Cannot merge value on a non-object (%s) for key: %s',
                get_debug_type($config->{$key}),
                $key
            ));
        }
        $array = (array)$config->{$key};
        $config->{$key} = array_merge($array, $value);
        $this->writeConfig($config);
    }

}
