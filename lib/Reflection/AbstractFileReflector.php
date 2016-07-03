<?php

namespace PhpBench\Reflection;

class AbstractFileReflector
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
