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

/**
 * Represents information about the VCS system used by the current working
 * directory.
 */
class Information implements \ArrayAccess, \IteratorAggregate
{
    private $name;
    private $information;

    /**
     */
    public function __construct(string $name, array $information)
    {
        $this->name = $name;
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
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->information[$offset];
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        throw new \BadMethodCallException(sprintf(
            'Environmental information is immutable. Tried to set key "%s" with value "%s"',
            $offset,
            $value
        ));
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->information);
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        throw new \BadMethodCallException(sprintf(
            'Environmental information is immutable. Tried to unset key "%s"',
            $offset
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->information);
    }

    public function toArray(): array
    {
        return $this->information;
    }

    private function flattenInformation(array $information, $prefix = ''): array
    {
        $transformed = [];

        foreach ($information as $key => $value) {
            $key = $prefix ? $prefix . '_' . $key : $key;

            if (is_array($value)) {
                $transformed = array_merge($transformed, $this->flattenInformation($value, $key));

                continue;
            }

            $transformed[$key] = $value;
        }

        return $transformed;
    }
}
