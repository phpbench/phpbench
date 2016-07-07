<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Reflection\Locator;

use BetterReflection\Identifier\Identifier;
use BetterReflection\SourceLocator\Located\LocatedSource;
use BetterReflection\SourceLocator\Type\AbstractSourceLocator;
use PhpBench\Benchmark\Remote\Launcher;

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

        return new LocatedSource(file_get_contents($file), $file);
    }

    private function locateFile($classFqn)
    {
        if (isset($this->locatedClasses[$classFqn])) {
            return $this->locatedClasses[$classFqn];
        }

        $classHierarchy = $this->launcher->payload(__DIR__ . '/template/locator.template', [
            'file' => $this->file,
            'class' => $classFqn,
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
