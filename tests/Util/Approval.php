<?php

namespace PhpBench\Tests\Util;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\SkippedWithMessageException;
use RuntimeException;

use function json_decode;

class Approval
{
    /**
     * @param string[] $sections
     */
    public function __construct(private readonly string $path, private array $sections, private readonly ?string $expected)
    {
    }

    public static function create(string $path, int $configCount, string $delimiter = '---'): self
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
                    $delimiter,
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

        return new self($path, array_values($parts), $expected);
    }

    /**
     * @return array<mixed,mixed>
     */
    public function getConfig(int $offset): array
    {
        $rawConfig = $this->getSection($offset);
        $config = json_decode($rawConfig, true);

        if (null === $config) {
            throw new RuntimeException(sprintf(
                'Invalid JSON config: "%s"',
                $rawConfig
            ));
        }

        return $config;
    }

    public function getSection(int $offset): string
    {
        if (!isset($this->sections[$offset])) {
            throw new RuntimeException(sprintf(
                'No section at offset "%s", have sections at offsets "%s"',
                $offset,
                implode('", "', array_keys($this->sections))
            ));
        }

        return $this->sections[$offset];
    }

    public function approve(string $actual, bool $force = false): void
    {
        if (null === $this->expected || $force || getenv('PHPBENCH_APPROVE')) {
            file_put_contents($this->path, implode("\n---\n", array_merge(
                array_map(function (string $section) {
                    return trim($section);
                }, $this->sections),
                [
                    $actual
                ]
            )));

            throw new SkippedWithMessageException(sprintf('Approval generated for "%s"', $this->path));
        }

        Assert::assertEquals(trim($this->expected), trim($actual));
    }
}
