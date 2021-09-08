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

class ReflectionClass
{
    public $attributes = [];
    public $path;
    public $interfaces = [];
    public $class;
    public $namespace;
    public $abstract = false;
    public $comment;
    /** @var array<string, ReflectionMethod> */
    public $methods = [];

    public function __construct(?string $path = null, ?string $class = null)
    {
        $this->path = $path;
        $this->class = $class;
    }
}
