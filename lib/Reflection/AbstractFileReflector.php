<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Reflection;

use BetterReflection\Reflector\ClassReflector;
use BetterReflection\SourceLocator\Type\SingleFileSourceLocator;

abstract class AbstractFileReflector implements FileReflectorInterface
{
    protected function getClassNameFromFile($file)
    {
        $locator = new SingleFileSourceLocator($file);
        $reflector = new ClassReflector($locator);

        $classes = $reflector->getAllClasses();

        if (empty($classes)) {
            return;
        }

        $class = reset($classes);

        return $class->getName();
    }
}
