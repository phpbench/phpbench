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

namespace PhpBench\Environment;

use ArrayAccess;
use http\Exception\InvalidArgumentException;
use IteratorAggregate;
use ReturnTypeWillChange;
use BadMethodCallException;
use ArrayIterator;

/**
 * Represents information about the VCS system used by the current working
 * directory.
 *
 * @immutable
 *
 * @implements ArrayAccess<string, mixed>
 * @implements IteratorAggregate<string, mixed>
 */
class Information implements ArrayAccess, IteratorAggregate
{
    /** @var array<string, scalar|null>  */
    private array $information;

    /**
     * @param array<string, mixed> $information
     */
    public function __construct(private readonly string $name, array $information)
    {
        $this->information = $this->flattenInformation($information);
    }

    /**
     * Return the name of this information, it should represent the domain of
     * the infomration, e.g. "vcs", "uname".
     *
     * If an information is mutually exclusive then it should use a standard
     * name representing the category of the thing (e.g. "vcs"). This allows
     * reports and such things to reference it reliably.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->information[$offset];
    }

    /**
     * {@inheritdoc}
     */
    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        throw new BadMethodCallException(sprintf(
            'Environmental information is immutable. Tried to set key "%s" with value "%s"',
            $offset,
            is_scalar($value) ? $value : get_debug_type($value)
        ));
    }

    /**
     * {@inheritdoc}
     */
    #[ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->information);
    }

    /**
     * {@inheritdoc}
     */
    #[ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        throw new BadMethodCallException(sprintf(
            'Environmental information is immutable. Tried to unset key "%s"',
            $offset
        ));
    }

    /**
     * @return ArrayIterator<string, scalar|null>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->information);
    }

    /**
     * @return array<string, scalar|null>
     */
    public function toArray(): array
    {
        return $this->information;
    }

    /**
     * @param array<string, mixed> $information
     *
     * @return array<string, scalar|null>
     */
    private function flattenInformation(array $information, string $prefix = ''): array
    {
        $transformed = [];

        foreach ($information as $key => $value) {
            $key = $prefix ? $prefix . '_' . $key : $key;

            if (is_array($value)) {
                $transformed = array_merge($transformed, $this->flattenInformation($value, $key));

                continue;
            }

            if (!is_scalar($value) && $value !== null) {
                throw new InvalidArgumentException(sprintf('Unsupported type %s', get_debug_type($value)));
            }

            $transformed[$key] = $value;
        }

        return $transformed;
    }
}
