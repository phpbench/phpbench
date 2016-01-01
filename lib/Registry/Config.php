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

/**
 * Configuration storage.
 * Throws exceptions when accessing undefined offsets.
 */
class Config extends \ArrayObject
{
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
}
