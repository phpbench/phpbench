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

namespace PhpBench\Model;

class ParameterSet extends \ArrayObject
{
    /**
     * @var int
     */
    private $name;

    public function __construct($name, array $parameters = [])
    {
        $this->name = $name;
        parent::__construct($parameters);
    }

    public function getName(): string
    {
        return (string) $this->name;
    }

    /**
     * @deprecated use getName instead
     */
    public function getIndex()
    {
        return $this->name;
    }
}
