<?php

namespace PhpBench\Reflection;

use BetterReflection\SourceLocator\Type\AbstractSourceLocator;
use PhpBench\Benchmark\Remote\Launcher;
use BetterReflection\Reflector\ClassReflector;
use PhpBench\Reflection\FileReflectorInterface;
use PhpBench\Reflection\Locator\RemoteSourceLocator;

class RemoteFileReflector extends AbstractFileReflector
{
    private $launcher;

    public function __construct(Launcher $launcher)
    {
        $this->launcher = $launcher;
    }

    public function reflectFile($file)
    {
        $locator = new RemoteSourceLocator($this->launcher, $file);
        $reflector = new ClassReflector($locator);

        $class = $this->getClassNameFromFile($file);

        if (null === $reflection = $reflector->reflect($class)) {
            throw new \InvalidArgumentException(sprintf(
                'Composer could not find class "%s" for file "%s", maybe the namespace is wrong?',
                $class, $file
            ));
        }

        return $reflection;
    }
}
