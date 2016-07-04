<?php

namespace PhpBench\Reflection;

use PhpBench\Reflection\FileReflectorInterface;
use BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use BetterReflection\Reflector\ClassReflector;

abstract class AbstractFileReflector implements FileReflectorInterface
{
    protected function getClassNameFromFile($file)
    {
        $locator = new SingleFileSourceLocator($file);
        $reflector = new ClassReflector($locator);

        $classes = $reflector->getAllClasses();

        if (empty($classes)) {
            return null;
        }

        $class = reset($classes);

        return $class->getName();
    }
}
