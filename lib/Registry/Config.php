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

use ArrayObject;

/**
 * Configuration storage.
 * Throws exceptions when accessing undefined offsets.
 *
 * @extends ArrayObject<string,mixed>
 */
class Config extends ArrayObject
{
    /**
     * All names must satisfy this regex.
     */
    const NAME_REGEX = '{^[0-9a-zA-Z_-]+$}';

    private $name;

    public function __construct($name, array $config)
    {
        if (!preg_match(self::NAME_REGEX, $name)) {
            throw new \InvalidArgumentException(sprintf(
                'Configuration names may only contain alpha-numeric characters, _ and -. Got "%s"',
                $name
            ));
        }
        $this->name = $name;
        parent::__construct($config);
    }

    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new \InvalidArgumentException(sprintf(
                'Configuration offset "%s" does not exist. Known offsets: "%s"',
                $offset,
                implode('", "', array_keys($this->getArrayCopy()))
            ));
        }

        return parent::offsetGet($offset);
    }

    public function getName()
    {
        return $this->name;
    }
}
