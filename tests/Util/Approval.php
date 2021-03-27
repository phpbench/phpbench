<?php

namespace PhpBench\Tests\Util;

use function json_decode;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\SkippedTestError;
use RuntimeException;

class Approval
{
    /**
     * @var array<array<mixed,mixed>>
     */
    private $configs;

    /**
     * @var string
     */
    private $expected;

    /**
     * @var string
     */
    private $path;

    /**
     * @param array<array<mixed,mixed>> $configs
     */
    public function __construct(string $path, array $configs, ?string $expected)
    {
        $this->configs = $configs;
        $this->expected = $expected;
        $this->path = $path;
    }

    public static function create(string $path, int $configCount): self
    {
        if (!file_exists($path)) {
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0777, true);
            }
            touch($path);
        }
        $parts = array_values(
            (array)array_filter(
                explode(
                    '---',
                    (string)file_get_contents($path),
                    $configCount
                )
            )
        );

        $expected = null;

        if (count($parts) === 1) {
            $expected = $parts[0];
            $parts = [];
        }

        if ($parts && isset($parts[$configCount - 1])) {
            $expected = $parts[$configCount - 1];
            unset($parts[$configCount - 1]);
        }

        return new self($path, array_map(function (string $jsonConfig): array {
            $config = json_decode($jsonConfig, true);

            if (null === $config) {
                throw new RuntimeException(sprintf(
                    'Invalid JSON config: "%s"',
                    $jsonConfig
                ));
            }

            return $config;
        }, $parts), $expected);
    }

    /**
     * @return array<mixed,mixed>
     */
    public function getConfig(int $offset): array
    {
        if (!isset($this->configs[$offset])) {
            throw new RuntimeException(sprintf(
                'No config at offset "%s"',
                $offset
            ));
        }

        return $this->configs[$offset];
    }

    public function approve(string $actual, bool $force = false): void
    {
        if (null === $this->expected || $force) {
            file_put_contents($this->path, implode("\n---\n", array_merge(
                array_map(function (array $config) {
                    return json_encode($config, JSON_PRETTY_PRINT);
                }, $this->configs),
                [
                    $actual
                ]
            )));

            throw new SkippedTestError(sprintf('Approval generated'));
        }

        Assert::assertEquals(trim($this->expected), trim($actual));
    }
}
