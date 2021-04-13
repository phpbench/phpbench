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

use ArrayObject;

/**
 * @extends ArrayObject<string,mixed>
 */
class ParameterSet extends ArrayObject
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param array<string,mixed> $parameters
     */
    public function __construct(string $name, array $parameters = [])
    {
        $this->name = $name;
        parent::__construct($parameters);
    }

    /**
     * @param array<string,mixed> $parameters
     */
    public static function create(string $name, array $parameters): self
    {
        return new self($name, $parameters);
    }

    public function getName(): string
    {
        return (string) $this->name;
    }

    /**
     * @deprecated use getName instead
     */
    public function getIndex(): string
    {
        return $this->name;
    }
}
