<?php

namespace PhpBench\Reflection\Locator;

use BetterReflection\SourceLocator\Type\AbstractSourceLocator;
use BetterReflection\Identifier\Identifier;

class RemoteSourceLocator extends AbstractSourceLocator
{
    private $launcher;
    private $file;
    private $locatedClasses = [];

    public function __construct(Launcher $launcher, $file)
    {
        parent::__construct();
        $this->launcher = $launcher;
        $this->file = $file;
    }

    protected function createLocatedSource(Identifier $identifier)
    {
        if ($identifier->isFunction()) {
            throw new \InvalidArgumentException(sprintf(
                'Trying to identify a function "%s" with PhpBench. ' .
                'Who would do such a thing?',
                $identifier->getName()
            ));
        }

        $classFqn = $identifier->getName();
        $file = $this->locateFile($classFqn);

        return [
            file_get_contents($file),
            $file
        ];
    }

    private function locateFile($classFqn)
    {
        if (isset($this->locatedClasses[$classFqn])) {
            return $this->locatedClasses[$classFqn];
        }

        $classHierarchy = $this->launcher->payload(__DIR__ . '/template/reflector.template', [
            'file' => $this->file,
            'class' => $identifier->getName()
        ])->launch();

        foreach ($classHierarchy as $classData) {
            $this->locatedClasses[$classData['class']] = $classData['file'];
        }

        if (!isset($this->locatedClasses[$classFqn])) {
            throw new \InvalidArgumentException(sprintf(
                'Could not locate class "%s"',
                $classFqn
            ));
        }

        return $this->locatedClasses[$classFqn];
    }
}
