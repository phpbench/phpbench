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

namespace PhpBench\Reflection;

/**
 * @todo make $class not nullable
 */
class ReflectionClass
{
    /** @var object[] */
    public $attributes = [];

    /** @var string|null */
    public $path;

    /** @var list<string> */
    public $interfaces = [];

    /** @var class-string|null */
    public $class;

    /** @var string|null */
    public $namespace;

    /** @var bool */
    public $abstract = false;

    /** @var string|false */
    public $comment = false;

    /** @var array<string, ReflectionMethod> */
    public $methods = [];

    /**
     * @param class-string|null $class
     */
    public function __construct(?string $path = null, ?string $class = null)
    {
        $this->path = $path;
        $this->class = $class;
    }

    /**
     * @return class-string
     */
    public function getClass(): string
    {
        if ($this->class === null) {
            throw new \UnexpectedValueException('class property is empty');
        }

        return $this->class;
    }
}
