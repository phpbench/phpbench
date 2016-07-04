<?php

namespace PhpBench\Reflection;

use Composer\Autoload\ClassLoader;
use BetterReflection\SourceLocator\Type\SourceLocator;
use BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use BetterReflection\Reflector\ClassReflector;
use BetterReflection\SourceLocator\Type\ComposerSourceLocator;
use PhpBench\Reflection\FileReflectorInterface;

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

        if (null === $reflection = $this->reflector->reflect($class)) {
            throw new \InvalidArgumentException(sprintf(
                'Composer could not find class "%s" for file "%s", maybe the namespace is wrong?',
                $class, $file
            ));
        }

        return $reflection;
    }
}
