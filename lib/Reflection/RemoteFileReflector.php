<?php

namespace PhpBench\Reflection;

use BetterReflection\SourceLocator\Type\AbstractSourceLocator;
use PhpBench\Benchmark\Remote\Launcher;
use BetterReflection\Reflector\ClassReflector;
use PhpBench\Reflection\FileReflectorInterface;
use PhpBench\Reflection\Locator\RemoteSourceLocator;

class RemoteFileReflector implements FileReflectorInterface
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
    }
}
