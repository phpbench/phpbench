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

namespace PhpBench\Assertion\Ast;

use PhpBench\Assertion\Exception\PropertyAccessError;

class PropertyAccess implements Value
{
    /**
     * @var array<string>
     */
    private $segments;

    /**
     * @param array<string> $segments
     */
    public function __construct(array $segments)
    {
        $this->segments = $segments;
    }

    public function segments(): array
    {
        return $this->segments;
    }

    /**
     * @return int|float
     *
     * @param array<string,mixed>|object|scalar $container
     * @param array<string> $segments
     */
    public static function resolvePropertyAccess(array $segments, $container)
    {
        $segment = array_shift($segments);
        $value = self::valueFromContainer($container, $segment);

        if (is_scalar($value)) {
            return $value;
        }

        return self::resolvePropertyAccess($segments, $value);
    }

    /**
     * @return int|float|object|array<string,mixed>
     *
     * @param array<string,mixed>|object|scalar $container
     */
    private static function valueFromContainer($container, string $segment)
    {
        if (is_array($container)) {
            if (!array_key_exists($segment, $container)) {
                throw new PropertyAccessError(sprintf(
                    'Array does not have key "%s", it has keys: "%s"',
                    $segment,
                    implode('", "', array_keys($container))
                ));
            }

            return $container[$segment];
            ;
        }
        
        if (is_object($container) && method_exists($container, $segment)) {
            return $container->$segment();
        }

        throw new PropertyAccessError(sprintf(
            'Could not access "%s" on "%s"',
            $segment,
            is_object($container) ? get_class($container) : gettype($container)
        ));
    }
}
