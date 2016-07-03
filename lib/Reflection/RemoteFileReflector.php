<?php

namespace PhpBench\Reflection;

use BetterReflection\SourceLocator\Type\AbstractSourceLocator;
use PhpBench\Benchmark\Remote\Launcher;
use BetterReflection\Reflector\ClassReflector;

class RemoteFileReflector extends AbstractSourceLocator
{
    private $launcher;

    public function __construct(Launcher $launcher)
    {
        parent::__construct();
        $this->launcher = $launcher;
    }

    public function reflectFile($file)
    {
        $locator = new RemoteSourceLocator($this->launcher, $file);
        $reflector = new ClassReflector($locator);
    }
}
