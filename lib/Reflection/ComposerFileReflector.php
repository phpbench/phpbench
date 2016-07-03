<?php

namespace PhpBench\Reflection;

use Composer\Autoload\ClassLoader;
use BetterReflection\SourceLocator\Type\SourceLocator;
use BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use BetterReflection\Reflector\ClassReflector;
use BetterReflection\SourceLocator\Type\ComposerSourceLocator;

class ComposerFileReflector extends AbstractFileReflector
{
    private $reflector;

    public function __construct(ClassLoader $classLoader)
    {
        $this->reflector = new ClassReflector(new ComposerSourceLocator($classLoader));
    }

    public function reflectFile($file)
    {
        $class = $this->getClassNameFromFile($file);

        return $this->reflector->reflect($class);
    }
}
